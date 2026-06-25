<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Input\Blog\Theme\ChangeThemeInput;
use App\Api\Console\Input\Blog\Theme\CheckThemeFileNameAvailableInput;
use App\Api\Console\Object\ThemeFileObject;
use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\ThemeFile;
use App\Service\Limit;
use App\Service\Theme\ThemeFilesService;
use App\Service\Theme\ThemeZipService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private ThemeFilesService $themeFilesService,
        private ThemeZipService $themeZipService,
    ) {
    }

    #[Route('/theme', methods: ['POST'])]
    public function uploadTheme(Request $request): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $zip = $request->files->get('zip');
        if (!$zip instanceof UploadedFile) {
            throw new UnprocessableEntityHttpException('The zip field is required');
        }
        if ($zip->getSize() !== false && $zip->getSize() > Limit::MAX_THEME_ZIP_SIZE) {
            throw new UnprocessableEntityHttpException('The zip file is too large');
        }

        $content = file_get_contents($zip->getPathname());
        $importer = $content !== false ? $this->themeFilesService->updateFilesFromZip($blog, $content) : null;

        if ($importer === null || !$importer->success()) {
            throw new UnprocessableEntityHttpException('Unable to import the theme');
        }

        return new JsonResponse($this->fileObjects($blog));
    }

    #[Route('/theme', methods: ['PATCH'])]
    public function changeTheme(
        #[MapRequestPayload] ChangeThemeInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        try {
            $this->themeZipService->copyThemeToBlog($blog, $input->name);
        } catch (\RuntimeException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($this->fileObjects($blog));
    }

    #[Route('/theme/download', methods: ['GET'])]
    public function downloadTheme(): Response
    {
        $blog = $this->blogAuthListener->getBlog();
        $zipContent = $this->themeZipService->exportZip($blog);

        $filename = 'hb-theme-of-' . $blog->getSubdomain() . '-' . date('Y-m-d') . '.zip';

        $response = new Response($zipContent);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/theme/files', methods: ['GET'])]
    public function getAllFiles(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        return new JsonResponse($this->fileObjects($blog));
    }

    #[Route('/theme/file', methods: ['POST'])]
    public function createFile(Request $request): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $body = $this->bodyParams($request);

        $folderValue = $body['folder'] ?? null;
        $folder = is_string($folderValue) ? ThemeFileFolder::tryFrom($folderValue) : null;

        $nameValue = $body['name'] ?? null;
        $name = is_string($nameValue) ? $nameValue : '';
        if ($name === '') {
            throw new UnprocessableEntityHttpException('The name field is required');
        }

        $contentValue = $body['content'] ?? null;
        $content = is_string($contentValue) ? $contentValue : '';

        $uploadedFile = $request->files->get('file');
        if ($uploadedFile instanceof UploadedFile) {
            if ($uploadedFile->getSize() !== false && $uploadedFile->getSize() > Limit::MAX_ASSET_FILE_SIZE) {
                throw new UnprocessableEntityHttpException('The file must not be greater than the allowed size');
            }
            $fileContent = file_get_contents($uploadedFile->getPathname());
            $content = $fileContent === false ? '' : $fileContent;
        }

        if ($this->themeFilesService->getFile($blog, $name, $folder) !== null) {
            throw new UnprocessableEntityHttpException('File already exists');
        }

        $file = $this->themeFilesService->createOrUpdateFile($blog, $folder, $name, $content);

        return new JsonResponse(new ThemeFileObject($file));
    }

    #[Route('/theme/file/{id}', methods: ['PATCH'])]
    public function updateFile(
        #[MapBlogEntity] ThemeFile $file,
        Request $request,
    ): JsonResponse {
        $body = $this->bodyParams($request);

        /** @var array{name?: ?string, content?: ?string} $updates */
        $updates = [];
        if (array_key_exists('name', $body) && (is_string($body['name']) || $body['name'] === null)) {
            $updates['name'] = $body['name'];
        }
        if (array_key_exists('content', $body) && (is_string($body['content']) || $body['content'] === null)) {
            $updates['content'] = $body['content'];
        }

        $file = $this->themeFilesService->updateFile($file, $updates);

        return new JsonResponse(new ThemeFileObject($file));
    }

    #[Route('/theme/file/{id}', methods: ['DELETE'])]
    public function deleteFile(#[MapBlogEntity] ThemeFile $file): JsonResponse
    {
        $this->themeFilesService->deleteFile($file);

        return new JsonResponse();
    }

    #[Route('/theme/file/name-available', methods: ['GET'])]
    public function isFileNameAvailable(
        #[MapQueryString] CheckThemeFileNameAvailableInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $file = $this->themeFilesService->getFile($blog, $input->name, $input->folder);

        return new JsonResponse([
            'available' => $file === null,
        ]);
    }

    /** @return ThemeFileObject[] */
    private function fileObjects(Blog $blog): array
    {
        return array_map(
            fn(ThemeFile $file) => new ThemeFileObject($file),
            $this->themeFilesService->getAllFilesOfBlog($blog),
        );
    }

    /**
     * Reads request params from either a JSON body or a multipart/form-data body.
     *
     * @return array<string, mixed>
     */
    private function bodyParams(Request $request): array
    {
        $contentType = $request->headers->get('Content-Type') ?? '';
        if (str_contains($contentType, 'multipart/form-data') || str_contains($contentType, 'application/x-www-form-urlencoded')) {
            /** @var array<string, mixed> */
            return $request->request->all();
        }

        $decoded = json_decode($request->getContent(), true);
        /** @var array<string, mixed> */
        return is_array($decoded) ? $decoded : [];
    }
}
