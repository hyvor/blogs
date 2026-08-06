<?php

namespace App\Service\Hosting\MessageHandler;

use App\Entity\Enum\HostingChangeStatus;
use App\Entity\HostingChange;
use App\Service\Hosting\HostingChangeService;
use App\Service\Hosting\Message\HostingChangeMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class HostingChangeMessageHandler
{
    use ClockAwareTrait;

    private const int MAX_ATTEMPTS = 3;
    private const int BASE_DELAY_MS = 60000;
    private const int DELAY_MULTIPLIER = 5;

    public function __construct(
        private ManagerRegistry $registry,
        private HostingChangeService $hostingChangeService,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(HostingChangeMessage $message): void
    {
        $em = $this->entityManager();
        $hostingChange = $em->find(HostingChange::class, $message->hostingChangeId);

        if ($hostingChange === null) {
            throw new UnrecoverableMessageHandlingException("HostingChanges {$message->hostingChangeId} not found");
        }

        try {
            $this->hostingChangeService->process($hostingChange);
        } catch (\Throwable $e) {
            $this->handleFailure($message, $e);
        }
    }

    private function handleFailure(HostingChangeMessage $message, \Throwable $e): void
    {
        // process() may have closed the EntityManager (Doctrine closes it on any exception
        // raised inside wrapInTransaction), so fetch a fresh one to record the failure
        $em = $this->entityManager();
        $hostingChange = $em->find(HostingChange::class, $message->hostingChangeId);

        if ($hostingChange === null) {
            return;
        }

        $retryCount = $hostingChange->getRetryCount() + 1;
        $hostingChange->setRetryCount($retryCount);
        $hostingChange->setUpdatedAt($this->now());

        if ($retryCount >= self::MAX_ATTEMPTS) {
            $hostingChange->setStatus(HostingChangeStatus::FAILED);
            $hostingChange->setErrorMessage($e->getMessage());
            $em->flush();
            return;
        }

        $em->flush();

        $delay = self::BASE_DELAY_MS * (self::DELAY_MULTIPLIER ** ($retryCount - 1));
        $this->bus->dispatch(new HostingChangeMessage($message->hostingChangeId), [new DelayStamp($delay)]);
    }

    private function entityManager(): EntityManagerInterface
    {
        $em = $this->registry->getManager();

        if (!$em instanceof EntityManagerInterface || !$em->isOpen()) {
            $em = $this->registry->resetManager();
        }

        assert($em instanceof EntityManagerInterface);
        return $em;
    }
}
