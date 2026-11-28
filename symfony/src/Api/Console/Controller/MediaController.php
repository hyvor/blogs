<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Media\GetMediaInput;
use App\Api\Console\Input\Media\SearchUnsplashInput;
use App\Api\Console\Input\Media\UpdateMediaInput;
use App\Api\Console\Input\Media\UploadMediaFromUrlInput;
use App\Api\Console\Object\MediaObject;
use App\Api\Console\Object\MediaObjectFactory;
use App\Api\Console\Object\UnsplashImageObject;
use App\Entity\Media;
use App\Service\Billing\UsageService;
use App\Service\Integration\Unsplash\UnsplashSearchException;
use App\Service\Integration\Unsplash\UnsplashService;
use App\Service\Limit;
use App\Service\Media\MediaException;
use App\Service\Media\MediaService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class MediaController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private MediaService $mediaService,
        private MediaObjectFactory $mediaObjectFactory,
        private UsageService $usageService,
        private UnsplashService $unsplashService,
    ) {}

    #[Route('/media', methods: ['GET'])]
    #[ScopeRequired(Scope::MEDIA_MANAGE)]
    #[OA\Get(
        description: 'Returns the media files of the blog.',
        summary: 'List media',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of media.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: MediaObject::class))),
    )]
    public function list(
        #[MapQueryString] GetMediaInput $input = new GetMediaInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $extensions = $input->extensions;
        if ($input->type === 'image') {
            $extensions = MediaService::IMAGE_EXTENSIONS;
        }

        $media = $this->mediaService->getMedia(
            $blog,
            $input->limit,
            $input->offset,
            $extensions,
            $input->search,
        );

        return new JsonResponse(array_map(
            fn(Media $m) => $this->mediaObjectFactory->create($m, $blog),
            $media,
        ));
    }

    #[Route('/media', methods: ['POST'])]
    #[ScopeRequired(Scope::MEDIA_UPLOAD)]
    #[OA\Post(
        description: 'Uploads a file to the blog media library. Send the request as `multipart/form-data` with the `file` field, and optionally `post_id` and `name`.',
        summary: 'Upload a file',
    )]
    #[OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(required: ['file'], properties: [new OA\Property(property: 'file', type: 'string', format: 'binary'), new OA\Property(property: 'post_id', type: 'integer'), new OA\Property(property: 'name', type: 'string')])))]
    #[OA\Response(
        response: 200,
        description: 'Returns the uploaded media.',
        content: new Model(type: MediaObject::class),
    )]
    public function upload(Request $request): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            throw new UnprocessableEntityHttpException('The file field is required.');
        }

        if ($this->usageService->storageLimitReached($blog, (int)$file->getSize())) {
            throw new UnprocessableEntityHttpException('Total storage limit exceeded. Please upgrade your plan.');
        }

        $fileSize = $file->getSize();
        if ($fileSize === false || $fileSize > Limit::MAX_MEDIA_UPLOAD_SIZE) {
            throw new UnprocessableEntityHttpException('The file must not be larger than the allowed limit.');
        }

        $postId = $request->request->has('post_id') ? (int)$request->request->get('post_id') : null;
        $fileName = $request->request->get('name');
        $fileName = is_string($fileName) && $fileName !== '' ? $fileName : null;

        if ($fileName !== null) {
            $this->validateFilename($fileName);
        }

        try {
            $media = $this->mediaService->uploadFile($blog, $file, $postId, $fileName);
        } catch (MediaException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($this->mediaObjectFactory->create($media, $blog));
    }

    #[Route('/media/from-url', methods: ['POST'])]
    #[ScopeRequired(Scope::MEDIA_UPLOAD)]
    #[OA\Post(
        description: 'Downloads a file from the given URL and adds it to the blog media library.',
        summary: 'Upload a file from a URL',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the uploaded media.',
        content: new Model(type: MediaObject::class),
    )]
    public function uploadFromUrl(
        #[MapRequestPayload] UploadMediaFromUrlInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->usageService->storageLimitReached($blog)) {
            throw new UnprocessableEntityHttpException('Total storage limit exceeded. Please upgrade your plan.');
        }

        try {
            $media = $this->mediaService->uploadFromUrl($blog, $input->url, $input->post_id);
        } catch (MediaException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($this->mediaObjectFactory->create($media, $blog));
    }

    #[Route('/media/{id}', methods: ['PATCH'])]
    #[ScopeRequired(Scope::MEDIA_MANAGE)]
    #[OA\Patch(
        description: 'Updates the details of a media file, such as its name.',
        summary: 'Update media',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated media.',
        content: new Model(type: MediaObject::class),
    )]
    public function update(
        #[MapBlogEntity] Media $media,
        #[MapRequestPayload] UpdateMediaInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $this->validateFilename($input->name);

        try {
            $media = $this->mediaService->updateName($media, $input->name);
        } catch (MediaException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($this->mediaObjectFactory->create($media, $blog));
    }

    #[Route('/media/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::MEDIA_MANAGE)]
    #[OA\Delete(
        description: 'Permanently deletes a media file.',
        summary: 'Delete media',
    )]
    #[OA\Response(response: 200, description: 'The media was deleted.')]
    public function delete(#[MapBlogEntity] Media $media): JsonResponse
    {
        $this->mediaService->deleteMedia($media);

        return new JsonResponse();
    }

    #[Route('/media/unsplash/search', methods: ['GET'])]
    #[ScopeRequired(Scope::MEDIA_MANAGE)]
    public function searchUnsplash(
        #[MapQueryString] SearchUnsplashInput $input,
    ): JsonResponse {
        try {
            $results = $this->unsplashService->search($input->search, $input->page);
        } catch (UnsplashSearchException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse(array_map(
            fn(array $image) => UnsplashImageObject::fromUnsplash($image),
            $results,
        ));
    }

    private function validateFilename(string $name): void
    {
        if (str_contains($name, '/')) {
            throw new UnprocessableEntityHttpException('Invalid filename: / is not allowed');
        }
    }
}
