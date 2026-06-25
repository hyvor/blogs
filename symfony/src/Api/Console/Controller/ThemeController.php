<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Input\Blog\Theme\ChangeThemeInput;
use App\Api\Console\Input\Blog\Theme\CheckThemeFileNameAvailableInput;
use App\Api\Console\Input\Blog\Theme\CreateThemeFileInput;
use App\Api\Console\Input\Blog\Theme\UpdateThemeFileInput;
use App\Api\Console\Object\ThemeFileObject;
use App\Entity\Blog;
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
    public function createFile(
        #[MapRequestPayload] CreateThemeFileInput $input,
        Request $request,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $content = $input->content;
        $uploadedFile = $request->files->get('file');
        if ($uploadedFile instanceof UploadedFile) {
            if ($uploadedFile->getSize() !== false && $uploadedFile->getSize() > Limit::MAX_ASSET_FILE_SIZE) {
                throw new UnprocessableEntityHttpException('The file must not be greater than the allowed size');
            }
            $fileContent = file_get_contents($uploadedFile->getPathname());
            $content = $fileContent === false ? '' : $fileContent;
        }

        if ($this->themeFilesService->isFileAllowedInFolder($input->folder, $input->name) === false) {
            throw new UnprocessableEntityHttpException("The file '$input->name' is not allowed in the specified folder");
        }

        if ($this->themeFilesService->getFile($blog, $input->name, $input->folder) !== null) {
            throw new UnprocessableEntityHttpException("File with name '$input->name' already exists in the specified folder");
        }

        $file = $this->themeFilesService->createOrUpdateFile($blog, $input->folder, $input->name, $content);

        return new JsonResponse(new ThemeFileObject($file));
    }

    #[Route('/theme/file/{id}', methods: ['PATCH'])]
    public function updateFile(
        #[MapBlogEntity] ThemeFile $file,
        #[MapRequestPayload] UpdateThemeFileInput $input,
    ): JsonResponse {

        /** @var array{name?: ?string, content?: ?string} $updates */
        $updates = [];
        if ($input->name !== null) {
            $updates['name'] = $input->name;
        }
        if ($input->content !== null) {
            $updates['content'] = $input->content;
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

}
