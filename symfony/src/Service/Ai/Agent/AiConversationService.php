<?php

namespace App\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageEvent;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\PostVariant;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Reads and manages persisted conversations for display in the Console (listing, deleting,
 * fetching a conversation's messages) - separate from AiAgentConversationService, which
 * only concerns itself with running/persisting a live agent call.
 */
class AiConversationService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /**
     * @return array{conversations: AiConversation[], has_more: bool}
     */
    public function getConversationsForBlog(Blog $blog, int $limit, int $offset, ?int $postVariantId = null): array
    {
        $queryBuilder = $this->em->getRepository(AiConversation::class)->createQueryBuilder('c')
            ->where('c.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('c.updated_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit + 1);

        if ($postVariantId !== null) {
            $queryBuilder
                ->andWhere('IDENTITY(c.post_variant) = :postVariantId')
                ->setParameter('postVariantId', $postVariantId);
        }

        /** @var AiConversation[] $conversations */
        $conversations = $queryBuilder->getQuery()->getResult();

        $hasMore = count($conversations) > $limit;
        if ($hasMore) {
            array_pop($conversations);
        }

        return ['conversations' => $conversations, 'has_more' => $hasMore];
    }

    public function deleteConversation(AiConversation $conversation): void
    {
        $this->em->remove($conversation);
        $this->em->flush();
    }

    /**
     * @return AiMessage[]
     */
    public function getMessages(AiConversation $conversation): array
    {
        /** @var AiMessage[] $messages */
        $messages = $this->em->getRepository(AiMessage::class)->createQueryBuilder('m')
            ->select('m')
            ->leftJoin('m.events', 'e')
            ->addSelect('e')
            ->leftJoin('e.post_variant', 'v')
            ->addSelect('v')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $messages;
    }

    /**
     * Distinct post variants referenced by any document_change event in this conversation, so
     * the frontend can show what's being edited (title, status) without a separate lookup.
     *
     * Get post variants involved in the
     *
     * @return PostVariant[]
     */
    public function getInvolvedPostVariants(AiConversation $conversation): array
    {
        /** @var PostVariant[] $postVariants */
        $postVariants = $this->em->getRepository(PostVariant::class)->createQueryBuilder('v')
            ->select('DISTINCT v')
            ->innerJoin(AiMessageEvent::class, 'e', 'WITH', 'e.post_variant = v')
            ->innerJoin('e.ai_message', 'm')
            ->where('m.conversation = :conversation')
            ->andWhere('e.type = :type')
            ->setParameter('conversation', $conversation)
            ->setParameter('type', AiMessageEventType::DOCUMENT_CHANGE)
            ->getQuery()
            ->getResult();

        return $postVariants;
    }

    public function getEvent(Blog $blog, int $eventId): ?AiMessageEvent
    {
        $event = $this->em->getRepository(AiMessageEvent::class)->createQueryBuilder('e')
            ->innerJoin('e.ai_message', 'm')
            ->innerJoin('m.conversation', 'c')
            ->where('e.id = :eventId')
            ->andWhere('c.blog = :blog')
            ->setParameter('eventId', $eventId)
            ->setParameter('blog', $blog)
            ->getQuery()
            ->getOneOrNullResult();

        return $event;
    }

}
