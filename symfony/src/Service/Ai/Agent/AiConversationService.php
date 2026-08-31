<?php

namespace App\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageChunk;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageChunkType;
use App\Entity\Enum\AiMessageRole;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Reads and manages persisted conversations for display in the Console (listing, deleting,
 * reconstructing a conversation's turns) - separate from AiAgentConversationService, which
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
    public function getConversationsForBlog(Blog $blog, int $limit, int $offset): array
    {
        /** @var AiConversation[] $conversations */
        $conversations = $this->em->getRepository(AiConversation::class)->createQueryBuilder('c')
            ->where('c.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('c.updated_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit + 1)
            ->getQuery()
            ->getResult();

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
     * Reconstructs a conversation's turns from the database into the same event shapes used
     * while streaming live (see AiAgentConversationService::toSseArray), so the frontend can
     * replay a loaded conversation through the exact same block-building logic it already has
     * for a live stream.
     *
     * @return list<array<string, mixed>>
     */
    public function getTurns(AiConversation $conversation): array
    {
        $messages = $this->em->getRepository(AiMessage::class)->findBy(
            ['conversation' => $conversation],
            ['id' => 'ASC'],
        );

        if ($messages === []) {
            return [];
        }

        /** @var AiMessageChunk[] $chunks */
        $chunks = $this->em->getRepository(AiMessageChunk::class)->createQueryBuilder('c')
            ->where('c.message IN (:messages)')
            ->setParameter('messages', $messages)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        /** @var array<int, AiMessageChunk[]> $chunksByMessageId */
        $chunksByMessageId = [];
        foreach ($chunks as $chunk) {
            $chunksByMessageId[$chunk->getMessage()->getId()][] = $chunk;
        }

        $turns = [];

        foreach ($messages as $message) {
            if ($message->getRole() === AiMessageRole::USER) {
                $turns[] = [
                    'role' => 'user',
                    'content' => $message->getContent(),
                ];
                continue;
            }

            $events = [];
            $thinkingOpen = false;

            foreach ($chunksByMessageId[$message->getId()] ?? [] as $chunk) {
                if ($chunk->getType() === AiMessageChunkType::TEXT) {
                    $events[] = ['type' => 'text', 'content' => $chunk->getContent()];
                } elseif ($chunk->getType() === AiMessageChunkType::THINKING) {
                    if (!$thinkingOpen) {
                        $thinkingOpen = true;
                        $events[] = ['type' => 'thinking_started'];
                    }
                    $events[] = ['type' => 'thinking', 'content' => $chunk->getContent()];
                } elseif ($chunk->getType() === AiMessageChunkType::EVENT) {
                    if ($chunk->getContent() === 'thinking_done') {
                        $thinkingOpen = false;
                    }
                    $events[] = $chunk->getEventPayload() ?? ['type' => $chunk->getContent()];
                }
            }

            $turns[] = [
                'role' => 'assistant',
                'events' => $events,
                'model' => $message->getModel(),
                'input_tokens' => $message->getInputTokens(),
                'output_tokens' => $message->getOutputTokens(),
                'total_tokens' => $message->getTotalTokens(),
            ];
        }

        return $turns;
    }
}
