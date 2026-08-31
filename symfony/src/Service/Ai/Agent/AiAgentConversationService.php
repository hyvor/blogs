<?php

namespace App\Service\Ai\Agent;

use App\Api\Console\Object\PostVariantObject;
use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageChunk;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageChunkType;
use App\Entity\Enum\AiMessageRole;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\Event\AgentErrorEvent;
use App\Service\Ai\Agent\Event\AgentEvent;
use App\Service\Ai\Agent\Event\ConversationStartedEvent;
use App\Service\Ai\Agent\Event\DocumentChangeEvent;
use App\Service\Ai\Agent\Event\DoneEvent;
use App\Service\Ai\Agent\Event\PostVariantSelectedEvent;
use App\Service\Ai\Agent\Event\TextEvent;
use App\Service\Ai\Agent\Event\ThinkingDoneEvent;
use App\Service\Ai\Agent\Event\ThinkingEvent;
use App\Service\Ai\Agent\Event\ThinkingStartedEvent;
use App\Service\Ai\Agent\Event\ToolCallCompletedEvent;
use App\Service\Ai\Agent\Event\ToolCallEventFactory;
use App\Service\Ai\Agent\Event\ToolCallStartedEvent;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallStart;
use Symfony\AI\Platform\Result\Stream\Delta\ToolInputDelta;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class AiAgentConversationService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private AiAgentService $aiAgentService,
        private ToolCallEventFactory $toolCallEventFactory,
        private AiAgentHistoryService $aiAgentHistoryService,
        private PermalinkService $permalinkService,
        private LoggerInterface $logger,
    ) {}

    public function getConversationForBlog(Blog $blog, int $id): ?AiConversation
    {
        return $this->em->getRepository(AiConversation::class)->findOneBy([
            'id' => $id,
            'blog' => $blog,
        ]);
    }

    /**
     * @return iterable<array<string, mixed>> SSE-ready event payloads. These are built from
     * the same typed AgentEvent classes that get persisted, so a conversation reconstructed
     * from the database later can be sent to the frontend in the exact same shape.
     */
    public function streamPrompt(
        Blog $blog,
        string $prompt,
        ?PostVariant $postVariant,
        ?AiConversation $existingConversation = null,
    ): iterable
    {
        if ($existingConversation !== null) {
            $conversation = $existingConversation;
            $history = $this->aiAgentHistoryService->buildMessageHistory($conversation);
        } else {
            $conversation = new AiConversation();
            $conversation->setBlog($blog);
            $conversation->setTitle(mb_strimwidth($prompt, 0, 255, ''));
            $conversation->setCreatedAt($this->now());
            $conversation->setUpdatedAt($this->now());
            $this->em->persist($conversation);
            $this->em->flush();

            $history = null;
        }

        yield $this->toSseArray(new ConversationStartedEvent($conversation->getId()));

        if ($postVariant !== null) {
            $postVariantObject = new PostVariantObject($postVariant, $this->permalinkService);
            yield $this->toSseArray(new PostVariantSelectedEvent($postVariantObject));
        }

        $userMessage = $this->createMessage($conversation, AiMessageRole::USER, $prompt);
        $this->createChunk($userMessage, AiMessageChunkType::TEXT, $prompt);

        $assistantMessage = $this->createMessage($conversation, AiMessageRole::ASSISTANT, '');
        $assistantText = '';
        $thinking = false;

        // consecutive text/thinking deltas are merged into one open chunk row; any other
        // delta (tool calls, thinking-complete) closes the run so the next text/thinking
        // delta (if any) starts a fresh row instead of reopening an older one
        $openChunk = null;
        $openChunkType = null;

        try {
            $agentCallResult = $this->aiAgentService->callAgent($blog, $prompt, $postVariant, $history);

            $content = $agentCallResult->getResult()->getContent();
            assert(is_iterable($content));

            foreach ($content as $delta) {
                if ($delta instanceof TextDelta) {
                    $text = (string) $delta;
                    $assistantText .= $text;

                    if ($text !== '') {
                        $openChunk = $this->appendOrCreateChunk(
                            $assistantMessage,
                            AiMessageChunkType::TEXT,
                            $text,
                            $openChunk,
                            $openChunkType,
                        );
                        $openChunkType = AiMessageChunkType::TEXT;
                    }

                    yield $this->toSseArray(new TextEvent($text));
                } elseif ($delta instanceof ThinkingDelta) {
                    if (!$thinking) {
                        $thinking = true;
                        yield $this->toSseArray(new ThinkingStartedEvent());
                    }

                    $thinkingContent = $delta->getThinking();
                    if ($thinkingContent !== '') {
                        $openChunk = $this->appendOrCreateChunk(
                            $assistantMessage,
                            AiMessageChunkType::THINKING,
                            $thinkingContent,
                            $openChunk,
                            $openChunkType,
                        );
                        $openChunkType = AiMessageChunkType::THINKING;
                    }

                    yield $this->toSseArray(new ThinkingEvent($thinkingContent));
                } elseif ($delta instanceof ThinkingComplete) {
                    $thinking = false;
                    $openChunk = null;
                    $openChunkType = null;

                    $thinkingDoneEvent = new ThinkingDoneEvent();
                    $this->createEventChunk($assistantMessage, $thinkingDoneEvent);
                    yield $this->toSseArray($thinkingDoneEvent);
                } elseif ($delta instanceof ToolCallStart) {
                    $openChunk = null;
                    $openChunkType = null;

                    yield $this->toSseArray(new ToolCallStartedEvent($delta->getName()));
                } elseif ($delta instanceof ToolCallComplete) {
                    $openChunk = null;
                    $openChunkType = null;

                    foreach ($delta->getToolCalls() as $toolCall) {
                        $toolCallEvent = $this->toolCallEventFactory->fromToolCall($toolCall);

                        if ($toolCallEvent !== null) {
                            $this->createEventChunk($assistantMessage, $toolCallEvent);
                            yield $this->toSseArray($toolCallEvent);
                        } else {
                            yield $this->toSseArray(new ToolCallCompletedEvent($toolCall->getName()));
                        }
                    }
                } elseif ($delta instanceof ToolInputDelta) {
                    yield [
                        'type' => 'tool_input',
                        'tool_name' => $delta->getName(),
                        'input' => $delta->getPartialJson(),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error('AI agent call failed', [
                'blog_id' => $blog->getId(),
                'conversation_id' => $conversation->getId(),
                'exception' => $e,
            ]);

            $errorEvent = new AgentErrorEvent(
                'The AI assistant ran into a problem and could not finish this request. Please try again.'
            );
            $this->createEventChunk($assistantMessage, $errorEvent);

            $assistantMessage->setContent($assistantText);
            $assistantMessage->setUpdatedAt($this->now());
            $conversation->setUpdatedAt($this->now());
            $this->em->flush();

            yield $this->toSseArray($errorEvent);
            yield $this->toSseArray(new DoneEvent());

            return;
        }

        $tokenUsage = $agentCallResult->getResult()->getMetadata()->get('token_usage');
        if ($tokenUsage instanceof TokenUsageInterface) {
            $assistantMessage->setPromptTokens($tokenUsage->getPromptTokens());
            $assistantMessage->setCompletionTokens($tokenUsage->getCompletionTokens());
            $assistantMessage->setTotalTokens($tokenUsage->getTotalTokens());
        }

        $assistantMessage->setContent($assistantText);
        $assistantMessage->setUpdatedAt($this->now());
        $conversation->setUpdatedAt($this->now());
        $this->em->flush();

        $documentOpsTool = $agentCallResult->getDocumentOpsTool();

        $cachedDocuments = $documentOpsTool->getCachedDocuments();

        foreach ($cachedDocuments as $postVariantId => $fetchedDocument) {
            if ($fetchedDocument->changed()) {
                $finalDocument = $documentOpsTool->getFinalDocument($postVariantId);

                yield $this->toSseArray(
                    new DocumentChangeEvent(
                        $postVariantId,
                        (string)json_encode($finalDocument->toArray()),
                    )
                );
            }
        }

        yield $this->toSseArray(new DoneEvent());
    }

    private function createMessage(AiConversation $conversation, AiMessageRole $role, string $content): AiMessage
    {
        $message = new AiMessage();
        $message->setConversation($conversation);
        $message->setRole($role);
        $message->setContent($content);
        $message->setCreatedAt($this->now());
        $message->setUpdatedAt($this->now());
        $this->em->persist($message);
        $this->em->flush();

        return $message;
    }

    private function createChunk(AiMessage $message, AiMessageChunkType $type, string $content): AiMessageChunk
    {
        $chunk = new AiMessageChunk();
        $chunk->setMessage($message);
        $chunk->setType($type);
        $chunk->setContent($content);
        $chunk->setCreatedAt($this->now());
        $chunk->setUpdatedAt($this->now());
        $this->em->persist($chunk);
        $this->em->flush();

        return $chunk;
    }

    /**
     * Appends to the currently open chunk if it's still the same type (text/thinking),
     * otherwise starts a new one.
     */
    private function appendOrCreateChunk(
        AiMessage $message,
        AiMessageChunkType $type,
        string $content,
        ?AiMessageChunk $openChunk,
        ?AiMessageChunkType $openChunkType,
    ): AiMessageChunk {
        if ($openChunk !== null && $openChunkType === $type) {
            $openChunk->setContent($openChunk->getContent() . $content);
            $openChunk->setUpdatedAt($this->now());
            $this->em->flush();

            return $openChunk;
        }

        return $this->createChunk($message, $type, $content);
    }

    private function createEventChunk(AiMessage $message, AgentEvent $event): void
    {
        $chunk = new AiMessageChunk();
        $chunk->setMessage($message);
        $chunk->setType(AiMessageChunkType::EVENT);
        $chunk->setContent($event->getType());
        $chunk->setEventPayload(['type' => $event->getType(), ...$event->getPayload()]);
        $chunk->setCreatedAt($this->now());
        $chunk->setUpdatedAt($this->now());
        $this->em->persist($chunk);
        $this->em->flush();
    }

    /**
     * @return array<string, mixed>
     */
    private function toSseArray(AgentEvent $event): array
    {
        return ['type' => $event->getType(), ...$event->getPayload()];
    }
}
