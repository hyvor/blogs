<?php

namespace App\Service\Ai\Agent;

use App\Api\Console\Object\Ai\AiConversationObject;
use App\Api\Console\Object\Ai\AiMessageEventObject;
use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageEvent;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageRole;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\Event\DocumentChangeEvent;
use App\Service\Ai\Agent\Event\EventAbstract;
use App\Service\Ai\Agent\Event\QueryEvent;
use App\Service\Ai\Agent\Event\TextEvent;
use App\Service\Ai\Agent\Event\ThoughtEvent;
use App\Service\Ai\Agent\EventOld\AgentErrorEvent;
use App\Service\Ai\Agent\EventOld\AgentEvent;
use App\Service\Ai\Agent\EventOld\DoneEvent;
use App\Service\Ai\AiModel;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallComplete;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class AiAgentConversationService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private AiAgentService $aiAgentService,
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
     * @return iterable<array<string, mixed>> SSE-ready event payloads.
     */
    public function streamPrompt(
        Blog $blog,
        string $prompt,
        ?PostVariant $postVariant,
        ?AiConversation $existingConversation = null,
    ): iterable
    {
        if ($existingConversation !== null) {
            $existingConversation->setUpdatedAt($this->now());
            $this->em->flush();

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

            yield [
                'type' => 'conversation_created',
                'conversation' => new AiConversationObject($conversation)
            ];

            $history = null;
        }

        $userMessage = $this->createMessage($conversation, AiMessageRole::USER);
        $userMessageEvent = new TextEvent($prompt);
        $this->createEvent($userMessage, $userMessageEvent);

        $assistantMessage = $this->createMessage($conversation, AiMessageRole::ASSISTANT);
        // buffers consecutive TextDelta chunks - flushed into a single 'text' AiMessageEvent
        // whenever a different event type interrupts it, or the stream ends
        $textBuffer = '';

        // TollCallComplete doesn't carry the tool's actual output that we want to save
        // so we send a callback to the QueryTool.
        // also, we cannot yield from the callback, so we queue them and then yiel on ToolCallComplete

        /**
         * @var AiMessageEvent[] $pendingQueryEvents
         */
        $pendingQueryEvents = [];

        /**
         * @param array<string, mixed> $input
         */
        $onQueryComplete = function (string $toolName, array $input, mixed $output) use ($assistantMessage, &$pendingQueryEvents): void {
            $event = $this->createEvent(
                $assistantMessage,
                new QueryEvent(
                    $toolName,
                    $input,
                    $output
                )
            );
            $pendingQueryEvents[] = $event;
        };

        try {
            $agentCallResult = $this->aiAgentService->callAgent($blog, $prompt, $postVariant, $history, $onQueryComplete);
            $assistantMessage->setModel($agentCallResult->getModel());

            $content = $agentCallResult->getResult()->getContent();
            assert(is_iterable($content));

            foreach ($content as $delta) {
                foreach ($pendingQueryEvents as $queryEvent) {
                    yield $this->sseArrayFromMessageEvent($queryEvent);
                }
                $pendingQueryEvents = [];

                if ($delta instanceof TextDelta) {

                    // we simply send this chunk to frontend
                    $text = (string) $delta;
                    $textBuffer .= $text;

                    yield [
                        'type' => 'text_chunk',
                        'content' => $text,
                    ];

                } elseif ($delta instanceof ThinkingDelta) {

                    $this->flushTextBuffer($assistantMessage, $textBuffer);
                    yield [
                        'type' => 'thinking_chunk',
                        'content' => $delta->getThinking(),
                    ];

                } elseif ($delta instanceof ThinkingComplete) {

                    $this->flushTextBuffer($assistantMessage, $textBuffer);

                    $thoughtEvent = new ThoughtEvent(
                        $delta->getThinking(),
                        $delta->getSignature()
                    );

                    $event = $this->createEvent($assistantMessage, $thoughtEvent);

                    yield [
                        'type' => 'event',
                        'event' => new AiMessageEventObject($event),
                    ];

                } elseif ($delta instanceof ToolCallComplete) {

                    $this->flushTextBuffer($assistantMessage, $textBuffer);

                    foreach ($pendingQueryEvents as $queryEvent) {
                        yield $this->sseArrayFromMessageEvent($queryEvent);
                    }
                    $pendingQueryEvents = [];

//                    foreach ($delta->getToolCalls() as $toolCall) {
//
//                        if (str_starts_with($toolCall->getName(), 'document_')) {
//                            //
//                        }
//
//                        // not persisted here - query tool calls are persisted via the
//                        // onQueryComplete callback (with their actual output), and document-ops
//                        // tool calls are collapsed into a single 'document_change' event below,
//                        // once the stream ends, instead of one row per op
//                        $toolCallEvent = $this->toolCallEventFactory->fromToolCall($toolCall);
//
//                        if ($toolCallEvent !== null) {
//                            yield $this->toSseArray($toolCallEvent);
//                        } else {
//                            yield $this->toSseArray(new ToolCallCompletedEvent($toolCall->getName()));
//                        }
//                    }
                }
            }

            // drain one last time
            $this->flushTextBuffer($assistantMessage, $textBuffer);
            foreach ($pendingQueryEvents as $queryEvent) {
                yield $this->sseArrayFromMessageEvent($queryEvent);
            }
            $pendingQueryEvents = [];

        } catch (\Throwable $e) {
            $this->logger->error('AI agent call failed', [
                'blog_id' => $blog->getId(),
                'conversation_id' => $conversation->getId(),
                'exception' => $e,
            ]);

            $errorEvent = new AgentErrorEvent(
                'The AI assistant ran into a problem and could not finish this request. Please try again.'
            );

            $this->flushTextBuffer($assistantMessage, $textBuffer);
            $assistantMessage->setUpdatedAt($this->now());
            $conversation->setUpdatedAt($this->now());
            $this->em->flush();

            yield $this->toSseArray($errorEvent);
            yield $this->toSseArray(new DoneEvent());

            return;
        }

        $tokenUsage = $agentCallResult->getResult()->getMetadata()->get('token_usage');
        if ($tokenUsage instanceof TokenUsageInterface) {
            $inputTokens = $tokenUsage->getPromptTokens();
            $outputTokens = $tokenUsage->getCompletionTokens();

            $assistantMessage->setInputTokens($inputTokens);
            $assistantMessage->setOutputTokens($outputTokens);
            $assistantMessage->setTotalTokens($tokenUsage->getTotalTokens());

            $model = AiModel::tryFrom($agentCallResult->getModel());
            if ($model !== null && $inputTokens !== null && $outputTokens !== null) {
                $inputCostCents = $model->getInputCostCents($inputTokens);
                $outputCostCents = $model->getOutputCostCents($outputTokens);

                $assistantMessage->setInputTokensUsdCost($inputCostCents);
                $assistantMessage->setOutputTokensUsdCost($outputCostCents);
                $assistantMessage->setTotalTokensUsdCost($inputCostCents + $outputCostCents);
            }
        }

        $assistantMessage->setUpdatedAt($this->now());
        $conversation->setUpdatedAt($this->now());
        $this->em->flush();

        $documentOpsTool = $agentCallResult->getDocumentOpsTool();

        $cachedDocuments = $documentOpsTool->getCachedDocuments();

        foreach ($cachedDocuments as $postVariantId => $fetchedDocument) {
            if ($fetchedDocument->changed()) {
                $finalDocument = $documentOpsTool->getFinalDocument($postVariantId);
                $documentContent = (string) json_encode($finalDocument->toArray());

                $event = $this->createEvent(
                    $assistantMessage,
                    new DocumentChangeEvent(
                        $documentContent,
                        count($fetchedDocument->getOps()),
                        $fetchedDocument->getPostVariantVersion()
                    )
                );

                yield $this->sseArrayFromMessageEvent($event);
            }
        }

        yield $this->toSseArray(new DoneEvent());
    }

    private function createMessage(AiConversation $conversation, AiMessageRole $role): AiMessage
    {
        $message = new AiMessage();
        $message->setConversation($conversation);
        $message->setRole($role);
        $message->setCreatedAt($this->now());
        $message->setUpdatedAt($this->now());
        $this->em->persist($message);
        $this->em->flush();

        return $message;
    }

    private function flushTextBuffer(AiMessage $message, string &$buffer): void
    {
        if ($buffer === '') {
            return;
        }

        $this->createEvent($message, new TextEvent($buffer));
        $buffer = '';
    }

    private function createEvent(AiMessage $message, EventAbstract $eventDto): AiMessageEvent
    {
        $event = new AiMessageEvent();
        $event->setAiMessage($message);
        $event->setType($eventDto->getType());
        $event->setCreatedAt($this->now());
        $eventDto->setEventProperties($event);
        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    /**
     * @return array<string, mixed>
     */
    private function toSseArray(AgentEvent $event): array
    {
        return ['type' => $event->getType(), ...$event->getPayload()];
    }

    private function sseArrayFromMessageEvent(AiMessageEvent $event): array
    {
        return [
            'type' => 'event',
            'event' => new AiMessageEventObject($event),
        ];
    }
}
