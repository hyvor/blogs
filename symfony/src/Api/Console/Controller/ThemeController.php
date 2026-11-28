<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Theme\ChangeThemeInput;
use App\Api\Console\Input\Theme\CheckThemeFileNameAvailableInput;
use App\Api\Console\Input\Theme\CreateThemeFileInput;
use App\Api\Console\Input\Theme\UpdateThemeFileInput;
use App\Api\Console\Object\ThemeFileObject;
use App\Entity\Blog;
use App\Entity\ThemeFile;
use App\Service\Limit;
use App\Service\Theme\Exception\ThemeExportException;
use App\Service\Theme\Exception\ThemeImportException;
use App\Service\Theme\ThemeFilesService;
use App\Service\Theme\ThemeService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private ThemeFilesService $themeFilesService,
        private ThemeService $themeService,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/theme', methods: ['POST'])]
    #[ScopeRequired(Scope::THEMES_WRITE)]
    #[OA\Post(
        description: 'Replaces the theme files of the blog with the contents of a zip file. Send the request as `multipart/form-data` with the `zip` field.',
        summary: 'Upload a theme',
    )]
    #[OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(required: ['zip'], properties: [new OA\Property(property: 'zip', type: 'string', format: 'binary')])))]
    #[OA\Response(
        response: 200,
        description: 'Returns the theme files and import logs.',
        content: new OA\JsonContent(properties: [new OA\Property(property: 'files', type: 'array', items: new OA\Items(ref: new Model(type: ThemeFileObject::class))), new OA\Property(property: 'logs', type: 'array', items: new OA\Items(type: 'string'))]),
    )]
    public function upload(Request $request): JsonResponse
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

        if ($content === false) {
            throw new UnprocessableEntityHttpException('Unable to read the zip file content');
        }

        try {
            $importer = $this->themeFilesService->updateFilesFromZip($blog, $content);
        } catch (ThemeImportException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse([
            'files' => $this->fileObjects($blog),
            'logs' => $importer->getLogs(),
        ]);
    }

    #[Route('/theme', methods: ['PATCH'])]
    #[ScopeRequired(Scope::THEMES_WRITE)]
    #[OA\Patch(
        description: 'Changes the active theme of the blog to one of the built-in themes.',
        summary: 'Change the theme',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the theme files of the new theme.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: ThemeFileObject::class))),
    )]
    public function update(
        #[MapRequestPayload] ChangeThemeInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $theme = $this->themeService->getThemeByName($input->name);
        if ($theme === null) {
            throw new UnprocessableEntityHttpException("Theme '$input->name' not found");
        }

        $version = $input->version !== null
            ? $this->themeService->getThemeVersion($theme, $input->version)
            : $this->themeService->getThemeLatestVersion($theme);

        if ($version === null) {
            throw new BadRequestHttpException('Theme version not found');
        }

        try {
            $this->themeFilesService->updateFilesFromThemeVersion($blog, $version);
        } catch (ThemeImportException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($this->fileObjects($blog));
    }

    #[Route('/theme/download', methods: ['GET'])]
    #[ScopeRequired(Scope::THEMES_READ)]
    #[OA\Get(
        description: 'Downloads all theme files of the blog as a zip file.',
        summary: 'Download the theme',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the theme as a zip file.',
        content: new OA\MediaType(mediaType: 'application/zip', schema: new OA\Schema(type: 'string', format: 'binary')),
    )]
    public function download(): Response
    {
        $blog = $this->blogAuthListener->getBlog();

        try {
            $zipContent = $this->themeFilesService->exportFilesToZip($blog);
        } catch (ThemeExportException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->logger->error('Failed to export theme zip', ['exception' => $e]);
            throw new UnprocessableEntityHttpException('Unable to export the theme as a zip file. Please try again later.');
        }

        $filename = 'hb-theme-of-' . $blog->getSubdomain() . '-' . date('Y-m-d') . '.zip';

        $response = new Response($zipContent);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/theme/files', methods: ['GET'])]
    #[ScopeRequired(Scope::THEMES_READ)]
    #[OA\Get(
        description: 'Returns all theme files of the blog.',
        summary: 'List theme files',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of theme files.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: ThemeFileObject::class))),
    )]
    public function listFiles(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        return new JsonResponse($this->fileObjects($blog));
    }

    #[Route('/theme/file', methods: ['POST'])]
    #[ScopeRequired(Scope::THEMES_WRITE)]
    #[OA\Post(
        description: 'Creates a new theme file.',
        summary: 'Create a theme file',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created file.',
        content: new Model(type: ThemeFileObject::class),
    )]
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
    #[ScopeRequired(Scope::THEMES_WRITE)]
    #[OA\Patch(
        description: 'Updates the content or name of a theme file.',
        summary: 'Update a theme file',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated file.',
        content: new Model(type: ThemeFileObject::class),
    )]
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
    #[ScopeRequired(Scope::THEMES_WRITE)]
    #[OA\Delete(
        description: 'Deletes a theme file.',
        summary: 'Delete a theme file',
    )]
    #[OA\Response(response: 200, description: 'The file was deleted.')]
    public function deleteFile(#[MapBlogEntity] ThemeFile $file): JsonResponse
    {
        $this->themeFilesService->deleteFile($file);

        return new JsonResponse();
    }

    #[Route('/theme/file/name-available', methods: ['GET'])]
    #[ScopeRequired(Scope::THEMES_READ)]
    #[OA\Get(
        description: 'Checks whether a file name can be used in the given folder.',
        summary: 'Check theme file name availability',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns whether the name is available.',
        content: new OA\JsonContent(properties: [new OA\Property(property: 'available', type: 'boolean')]),
    )]
    public function checkFileNameAvailability(
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
