<?php

namespace App\Service\Export\MessageHandler;

use App\Entity\Export;
use App\Service\Export\ExportService;
use App\Service\Export\Message\ExportMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class ExportMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private ExportService $exportService,
    ) {
    }

    public function __invoke(ExportMessage $message): void
    {
        $export = $this->em->find(Export::class, $message->exportId);

        if ($export === null) {
            throw new UnrecoverableMessageHandlingException("Export {$message->exportId} not found");
        }

        $this->exportService->runExport($export);
    }
}
