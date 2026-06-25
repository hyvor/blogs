<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Object\ExportObject;
use App\Entity\Enum\ExportFormat;
use App\Service\Export\ExportService;
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
    public function getExports(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $exports = array_map(
            fn($export) => new ExportObject($export),
            $this->exportService->getExports($blog),
        );

        return new JsonResponse($exports);
    }

    #[Route('/data/export', methods: ['POST'])]
    public function export(): JsonResponse
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
