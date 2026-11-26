<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Object\ExportObject;
use App\Entity\Enum\ExportFormat;
use App\Service\Export\ExportService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ExportController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private ExportService $exportService,
    ) {
    }

    #[Route('/data/exports', methods: ['GET'])]
    #[ScopeRequired(Scope::EXPORT_MANAGE)]
    #[OA\Get(
        description: 'Returns the data exports of the blog.',
        summary: 'List exports',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of exports.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: ExportObject::class))),
    )]
    public function list(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $exports = array_map(
            fn($export) => new ExportObject($export),
            $this->exportService->getExports($blog),
        );

        return new JsonResponse($exports);
    }

    #[Route('/data/export', methods: ['POST'])]
    #[ScopeRequired(Scope::EXPORT_MANAGE)]
    #[OA\Post(
        description: 'Starts a new export of the blog data. The export is processed in the background.',
        summary: 'Create an export',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created export.',
        content: new Model(type: ExportObject::class),
    )]
    public function create(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->exportService->hasPendingExports($blog)) {
            throw new UnprocessableEntityHttpException(
                'There is already a pending export for this blog. Please wait until it is finished.',
            );
        }

        $export = $this->exportService->createExport($blog, ExportFormat::HYVOR_BLOGS);

        return new JsonResponse(new ExportObject($export));
    }
}
