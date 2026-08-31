<?php

namespace App\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageThinking;
use App\Entity\AiMessageToolCall;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageRole;
use App\Service\Ai\Agent\Event\AgentEvent;
use App\Service\Ai\Agent\Event\ThinkingDoneEvent;
use App\Service\Ai\Agent\Event\ThinkingStartedEvent;
use App\Service\Ai\Agent\Event\ToolCallCompletedEvent;
use App\Service\Ai\Agent\Event\ToolCallEventFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Platform\Result\ToolCall;

/**
 * Reads and manages persisted conversations for display in the Console (listing, deleting,
 * reconstructing a conversation's turns) - separate from AiAgentConversationService, which
 * only concerns itself with running/persisting a live agent call.
 */
class AiConversationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ToolCallEventFactory $toolCallEventFactory,
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
     * for a live stream. Only the completed thinking summary and the raw tool calls are kept
     * per message (not the live per-delta stream), so a reconstructed turn always renders its
     * thinking blocks, then its tool calls, then its final text - not necessarily the exact
     * live interleaving of a turn that thought/called-tools/wrote-text more than once.
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

        /** @var AiMessageThinking[] $thinkingRows */
        $thinkingRows = $this->em->getRepository(AiMessageThinking::class)->createQueryBuilder('t')
            ->where('t.ai_message IN (:messages)')
            ->setParameter('messages', $messages)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();

        /** @var array<int, AiMessageThinking[]> $thinkingByMessageId */
        $thinkingByMessageId = [];
        foreach ($thinkingRows as $thinking) {
            $thinkingByMessageId[$thinking->getAiMessage()->getId()][] = $thinking;
        }

        /** @var AiMessageToolCall[] $toolCallRows */
        $toolCallRows = $this->em->getRepository(AiMessageToolCall::class)->createQueryBuilder('c')
            ->where('c.ai_message IN (:messages)')
            ->setParameter('messages', $messages)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        /** @var array<int, AiMessageToolCall[]> $toolCallsByMessageId */
        $toolCallsByMessageId = [];
        foreach ($toolCallRows as $toolCall) {
            $toolCallsByMessageId[$toolCall->getAiMessage()->getId()][] = $toolCall;
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

            foreach ($thinkingByMessageId[$message->getId()] ?? [] as $thinking) {
                $events[] = $this->toSseArray(new ThinkingStartedEvent());
                $events[] = ['type' => 'thinking', 'content' => $thinking->getSummary()];
                $events[] = $this->toSseArray(new ThinkingDoneEvent());
            }

            foreach ($toolCallsByMessageId[$message->getId()] ?? [] as $toolCall) {
                $reconstructed = new ToolCall('', $toolCall->getToolName(), $toolCall->getArguments());
                $toolCallEvent = $this->toolCallEventFactory->fromToolCall($reconstructed);

                $events[] = $toolCallEvent !== null
                    ? $this->toSseArray($toolCallEvent)
                    : $this->toSseArray(new ToolCallCompletedEvent($toolCall->getToolName()));
            }

            if ($message->getContent() !== '') {
                $events[] = ['type' => 'text', 'content' => $message->getContent()];
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

    /**
     * @return array<string, mixed>
     */
    private function toSseArray(AgentEvent $event): array
    {
        return ['type' => $event->getType(), ...$event->getPayload()];
    }
}
