<?php

namespace App\Api\Cli\Controller;

use App\Entity\Enum\BlogType;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Blog\BlogService;
use App\Service\Theme\ThemeFilesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class CliController
{
    public function __construct(
        private BlogService $blogService,
        private ThemeFilesService $themeFilesService,
    ) {
    }

    #[Route('/files', methods: ['PATCH'])]
    public function updateFiles(
        Request $request,
        string $subdomain,
        #[MapRequestPayload] UpdateFilesInput $input,
    ): JsonResponse {
        $blog = $this->blogService->getBlogBySubdomain($subdomain);

        if ($blog === null) {
            throw new UnprocessableEntityHttpException('Invalid subdomain');
        }

        if ($blog->getType() !== BlogType::DEV) {
            throw new UnprocessableEntityHttpException('Please use a DEV blog');
        }

        if ($input->reset) {
            $this->themeFilesService->deleteAllFiles($blog);
        }

        foreach ($input->files as $path => $content) {
            $path = trim($path, '/');
            $split = explode('/', $path);

            $file = $split[1] ?? $split[0];
            $folder = count($split) > 1 ? ThemeFileFolder::tryFrom($split[0]) : null;

            if ($file) {
                $this->themeFilesService->createOrUpdateFile(
                    $blog,
                    $folder,
                    $file,
                    base64_decode((string) $content),
                );
            }
        }

        return new JsonResponse();
    }
}
