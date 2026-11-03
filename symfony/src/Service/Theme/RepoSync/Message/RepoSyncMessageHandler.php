<?php

namespace App\Service\Theme\RepoSync\Message;

use App\Service\Theme\Exception\ThemeImportException;
use App\Service\Theme\RepoSync\Exception\RepoSyncException;
use App\Service\Theme\RepoSync\RepoSyncService;
use App\Service\User\Exception\HyvorUserNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RepoSyncMessageHandler
{
    public function __construct(
        private RepoSyncService $repoSyncService,
    ) {
    }

    /**
     * @throws RepoSyncException
     * @throws ThemeImportException
     * @throws HyvorUserNotFoundException
     */
    public function __invoke(RepoSyncMessage $message): void
    {
        $this->repoSyncService->downloadAndSync($message->createPreviewBlogs);
    }
}
