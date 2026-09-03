<?php

namespace App\Service\Ai\Agent\MessageHandler;

use App\Entity\AiConversation;
use App\Service\Ai\Agent\Message\DeleteOldAiConversationsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteOldAiConversationsMessageHandler
{
    use ClockAwareTrait;

    private const int RETENTION_DAYS = 30;

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(DeleteOldAiConversationsMessage $message): void
    {
        $before = $this->now()->modify('-' . self::RETENTION_DAYS . ' days');

        $this->em->createQueryBuilder()
            ->delete(AiConversation::class, 'c')
            ->where('c.updated_at < :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }
}
