<?php

namespace App\Api\Misc;

use League\Flysystem\Filesystem;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MiscController extends AbstractController
{

    public function __construct(
        private Filesystem $filesystem,
    ) {}

    #[Route('/api/health', methods: ['GET'])]
    public function healthCheck(): Response
    {
        return new Response('OK', 200);
    }

    #[Route('/api/media/{path}', methods: ['GET'], requirements: ['path' => '.+'])]
    public function serveMedia(string $path): Response
    {
        try {
            if (!$this->filesystem->fileExists($path)) {
                return new Response(null, 404);
            }
        } catch (UnableToCheckExistence) {
            return new Response(null, 404);
        }

        try {
            $mimeType = $this->filesystem->mimeType($path);
            $lastModified = $this->filesystem->lastModified($path);
            $content = $this->filesystem->read($path);
        } catch (UnableToRetrieveMetadata | UnableToReadFile) {
            return new Response(null, 404);
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', $mimeType);
        $response->setPublic();
        $response->setMaxAge(31536000);
        $response->setEtag(md5($path . $lastModified));

        return $response;
    }
}
