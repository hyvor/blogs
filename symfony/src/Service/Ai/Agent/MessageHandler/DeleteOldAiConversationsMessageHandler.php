<?php

namespace App\Service\Ai\Agent\MessageHandler;

use App\Entity\AiConversation;
use App\Service\Ai\Agent\Message\DeleteOldAiConversationsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Conversations older than the retention period are hard-deleted. ai_messages and
 * ai_message_chunks both have ON DELETE CASCADE foreign keys back to ai_conversations
 * (see Version20260501000000), so deleting the conversation row is enough to remove its
 * messages and events too - no need to delete them separately here.
 */
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
