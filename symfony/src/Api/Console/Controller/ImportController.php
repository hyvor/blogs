<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Object\Import\ImportObject;
use App\Service\Import\ImportService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[ScopeRequired(Scope::IMPORT_MANAGE)]
    #[OA\Get(
        description: 'Returns the data imports of the blog.',
        summary: 'List imports',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of imports.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: ImportObject::class))),
    )]
    public function list(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $imports = array_map(
            fn($import) => new ImportObject($import),
            $this->importService->getImports($blog),
        );

        return new JsonResponse($imports);
    }
}
