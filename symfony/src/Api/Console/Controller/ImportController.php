<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Object\Import\ImportObject;
use App\Service\Import\ImportService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ImportController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private ImportService $importService,
    ) {
    }

    #[Route('/data/imports', methods: ['GET'])]
    public function getImports(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $imports = array_map(
            fn($import) => new ImportObject($import),
            $this->importService->getImports($blog),
        );

        return new JsonResponse($imports);
    }
}
