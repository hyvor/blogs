<?php

namespace App\MessageHandler;

use App\Entity\HostingChanges;
use App\Message\HostingChangeMessage;
use App\Service\Blog\Hosting\HostingChangeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class HostingChangeMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private HostingChangeService $hostingChangeService,
    ) {
    }

    public function __invoke(HostingChangeMessage $message): void
    {
        $hostingChange = $this->em->find(HostingChanges::class, $message->hostingChangeId);

        if ($hostingChange === null) {
            throw new UnrecoverableMessageHandlingException("HostingChanges {$message->hostingChangeId} not found");
        }

        $this->hostingChangeService->process($hostingChange);
    }
}
