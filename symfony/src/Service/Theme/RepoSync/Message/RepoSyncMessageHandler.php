<?php

namespace App\Service\Theme\RepoSync\Message;

use App\Service\Theme\RepoSync\Exception\RepoSyncException;
use App\Service\Theme\RepoSync\RepoSyncService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RepoSyncMessageHandler
{
    public function __construct(
        private RepoSyncService $repoSyncService,
    ) {
    }

    /** @throws RepoSyncException */
    public function __invoke(RepoSyncMessage $message): void
    {
        $this->repoSyncService->downloadAndSync($message->createPreviewBlogs);
    }
}
