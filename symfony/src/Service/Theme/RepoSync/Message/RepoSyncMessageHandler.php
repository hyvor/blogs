<?php

namespace App\Service\Theme\RepoSync\Message;

use App\Service\Theme\RepoSync\RepoSyncService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RepoSyncMessageHandler
{
    public function __construct(
        private RepoSyncService $repoSyncService,
    ) {
    }

    public function __invoke(RepoSyncMessage $message): void
    {
        $this->repoSyncService->downloadAndSync();
    }
}
