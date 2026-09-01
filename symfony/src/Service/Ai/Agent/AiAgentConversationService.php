<?php

namespace App\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageThinking;
use App\Entity\AiMessageToolCall;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageRole;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\Event\AgentErrorEvent;
use App\Service\Ai\Agent\Event\AgentEvent;
use App\Service\Ai\Agent\Event\DocumentChangeEvent;
use App\Service\Ai\Agent\Event\DoneEvent;
use App\Service\Ai\Agent\Event\StreamOnly\ConversationCreatedEvent;
use App\Service\Ai\Agent\Event\TextEvent;
use App\Service\Ai\Agent\Event\ThinkingDoneEvent;
use App\Service\Ai\Agent\Event\ThinkingEvent;
use App\Service\Ai\Agent\Event\ThinkingStartedEvent;
use App\Service\Ai\Agent\Event\ToolCallCompletedEvent;
use App\Service\Ai\Agent\Event\ToolCallEventFactory;
use App\Service\Ai\Agent\Event\ToolCallStartedEvent;
use App\Service\Ai\AiModel;
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

            yield $this->toSseArray(new ConversationCreatedEvent($conversation->getId(), $conversation->getTitle()));
            $history = null;
        }

        $this->createMessage($conversation, AiMessageRole::USER, $prompt);

        $assistantMessage = $this->createMessage($conversation, AiMessageRole::ASSISTANT, '');
        $assistantText = '';

        try {
            $agentCallResult = $this->aiAgentService->callAgent($blog, $prompt, $postVariant, $history);
            $assistantMessage->setModel($agentCallResult->getModel());

            $content = $agentCallResult->getResult()->getContent();
            assert(is_iterable($content));

            foreach ($content as $delta) {
                if ($delta instanceof TextDelta) {
                    $text = (string) $delta;
                    $assistantText .= $text;

                    yield $this->toSseArray(new TextEvent($text));
                } elseif ($delta instanceof ThinkingDelta) {
                    yield $this->toSseArray(new ThinkingEvent($delta->getThinking()));
                } elseif ($delta instanceof ThinkingComplete) {
                    $this->createThinking($assistantMessage, $delta->getThinking(), $delta->getSignature());
                    yield $this->toSseArray(new ThinkingDoneEvent($delta->getThinking()));
                } elseif ($delta instanceof ToolCallStart) {
                    yield $this->toSseArray(new ToolCallStartedEvent($delta->getName()));
                } elseif ($delta instanceof ToolCallComplete) {
                    foreach ($delta->getToolCalls() as $toolCall) {
                        $this->createToolCall($assistantMessage, $toolCall->getName(), $toolCall->getArguments());

                        $toolCallEvent = $this->toolCallEventFactory->fromToolCall($toolCall);

                        if ($toolCallEvent !== null) {
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

    private function createThinking(AiMessage $message, string $summary, ?string $signature): void
    {
        $thinking = new AiMessageThinking();
        $thinking->setAiMessage($message);
        $thinking->setSummary($summary);
        $thinking->setSignature($signature);
        $thinking->setCreatedAt($this->now());
        $thinking->setUpdatedAt($this->now());
        $this->em->persist($thinking);
        $this->em->flush();
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function createToolCall(AiMessage $message, string $toolName, array $arguments): void
    {
        $toolCall = new AiMessageToolCall();
        $toolCall->setAiMessage($message);
        $toolCall->setToolName($toolName);
        $toolCall->setArguments($arguments);
        $toolCall->setCreatedAt($this->now());
        $toolCall->setUpdatedAt($this->now());
        $this->em->persist($toolCall);
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
