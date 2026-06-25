<?php

namespace App\MessageHandler;

use App\Message\RepoSyncMessage;
use App\Service\Theme\RepoSync\RepoSyncService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RepoSyncMessageHandler
{
    public function __construct(
        private RepoSyncService $repoSyncService,
    ) {
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \RuntimeException
     * @throws \TypeError
     * @throws \ValueError
     * @throws \Exception
     */
    public function __invoke(RepoSyncMessage $message): void
    {
        $this->repoSyncService->downloadAndSync();
    }
}
