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
use App\Api\Console\Object\MediaObjectFactory;
use App\Api\Console\Object\UnsplashImageObject;
use App\Entity\Media;
use App\Service\Billing\UsageService;
use App\Service\Integration\Unsplash\UnsplashSearchException;
use App\Service\Integration\Unsplash\UnsplashService;
use App\Service\Limit;
use App\Service\Media\MediaService;
use App\Service\Media\MediaUploadException;
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
    public function getMedia(
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
    public function uploadFile(Request $request): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->usageService->storageLimitReached($blog)) {
            throw new UnprocessableEntityHttpException('Total storage limit exceeded. Please upgrade your plan.');
        }

        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            throw new UnprocessableEntityHttpException('The file field is required.');
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

        $media = $this->mediaService->uploadFile($blog, $file, $postId, $fileName);

        return new JsonResponse($this->mediaObjectFactory->create($media, $blog));
    }

    #[Route('/media/from-url', methods: ['POST'])]
    #[ScopeRequired(Scope::MEDIA_UPLOAD)]
    public function uploadFileFromUrl(
        #[MapRequestPayload] UploadMediaFromUrlInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->usageService->storageLimitReached($blog)) {
            throw new UnprocessableEntityHttpException('Total storage limit exceeded. Please upgrade your plan.');
        }

        try {
            $media = $this->mediaService->uploadFromUrl($blog, $input->url, $input->post_id);
        } catch (MediaUploadException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($this->mediaObjectFactory->create($media, $blog));
    }

    #[Route('/media/{id}', methods: ['PATCH'])]
    #[ScopeRequired(Scope::MEDIA_MANAGE)]
    public function updateMedia(
        #[MapBlogEntity] Media $media,
        #[MapRequestPayload] UpdateMediaInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $this->validateFilename($input->name);

        $media = $this->mediaService->updateName($media, $input->name);

        return new JsonResponse($this->mediaObjectFactory->create($media, $blog));
    }

    #[Route('/media/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::MEDIA_MANAGE)]
    public function deleteFile(#[MapBlogEntity] Media $media): JsonResponse
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
