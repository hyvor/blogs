<?php

namespace App\Tests\Service\Ai\Agent;

use App\Api\Console\Object\Ai\AiConversationObject;
use App\Api\Console\Object\Ai\AiMessageEventObject;
use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageEvent;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageEventDocumentChangeStatus;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\Enum\AiMessageRole;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\AiAgentConversationService;
use App\Service\Ai\Agent\MessageHistoryBuilder;
use App\Service\Ai\Agent\AiAgentService;
use App\Service\Ai\Agent\Tool\AgentCallResult;
use App\Service\Ai\Agent\Tool\DocumentOps\DocumentOpsTool;
use App\Service\Ai\AiModel;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNamespace;
use Psr\Log\NullLogger;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Metadata\Metadata;
use Symfony\AI\Platform\Result\RawResultInterface;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallStart;
use Symfony\AI\Platform\Result\ToolCall;
use Symfony\AI\Platform\TokenUsage\TokenUsage;

#[CoversClass(AiAgentConversationService::class)]
#[CoversClass(AgentCallResult::class)]
#[CoversNamespace('App\Service\Ai\Agent\Event')]
class AiAgentConversationServiceTest extends KernelTestCase
{

    private function createPostVariant(): PostVariant
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOneFor($blog);
        $post = PostFactory::createOneFor($blog);
        return PostVariantFactory::createOneFor($post, language: $language);
    }

    /**
     * @param array<int, object|\Closure> $deltas
     */
    private function buildService(
        PostVariant $postVariant,
        array $deltas,
        ?DocumentOpsTool $documentOpsTool = null,
        ?Metadata $metadata = null,
        ?\Throwable $throws = null,
        AiModel $model = AiModel::CLAUDE_SONNET_5,
    ): AiAgentConversationService {
        $documentOpsTool ??= new DocumentOpsTool(
            $postVariant->getPost()->getBlog(),
            $this->getService(PostService::class),
        );

        $fakeAiAgentService = new class ($deltas, $metadata ?? new Metadata(), $documentOpsTool, $throws, $model) extends AiAgentService {
            /**
             * @param array<int, object|\Closure> $deltas
             */
            public function __construct(
                private array $deltas,
                private Metadata $metadata,
                private DocumentOpsTool $documentOpsTool,
                private ?\Throwable $throws,
                private AiModel $model,
            ) {
            }

            public function callAgent(
                Blog $blog,
                string $prompt,
                ?PostVariant $postVariant,
                ?MessageBag $history = null,
                ?\Closure $onQueryComplete = null,
            ): AgentCallResult {
                if ($this->throws !== null) {
                    throw $this->throws;
                }

                $deltas = $this->deltas;

                $result = new class ($deltas, $this->metadata, $onQueryComplete) implements ResultInterface {
                    private ?RawResultInterface $rawResult = null;

                    /**
                     * @param array<int, object|\Closure> $deltas
                     */
                    public function __construct(
                        private array $deltas,
                        private Metadata $metadata,
                        private ?\Closure $onQueryComplete,
                    ) {
                    }

                    public function getContent(): iterable
                    {
                        foreach ($this->deltas as $delta) {
                            if ($delta instanceof \Closure) {
                                $delta($this->onQueryComplete);
                                continue;
                            }

                            yield $delta;
                        }
                    }

                    public function getRawResult(): ?RawResultInterface
                    {
                        return $this->rawResult;
                    }

                    public function setRawResult(RawResultInterface $rawResult): void
                    {
                        $this->rawResult = $rawResult;
                    }

                    public function getMetadata(): Metadata
                    {
                        return $this->metadata;
                    }
                };

                return new AgentCallResult($result, $this->documentOpsTool, $this->model);
            }
        };

        return new AiAgentConversationService(
            $this->getService(EntityManagerInterface::class),
            $fakeAiAgentService,
            new MessageHistoryBuilder($this->getService(EntityManagerInterface::class)),
            new NullLogger(),
        );
    }

    public function test_persists_conversation_with_user_and_assistant_text_and_returns_sse_events(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [
            new TextDelta('Hello'),
            new TextDelta(' world'),
        ]);

        $events = iterator_to_array($service->streamPrompt($blog, 'Say hello', $postVariant));

        $this->assertSame(
            ['conversation_created', 'text_chunk', 'text_chunk', 'done'],
            array_column($events, 'type'),
        );

        $this->assertInstanceOf(AiConversationObject::class, $events[0]['conversation']);
        $this->assertSame('Say hello', $events[0]['conversation']->title);

        $this->assertSame('Hello', $events[1]['content']);
        $this->assertSame(' world', $events[2]['content']);

        $conversations = $this->getEm()->getRepository(AiConversation::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $conversations);
        $conversation = $conversations[0];

        $this->assertSame('Say hello', $conversation->getTitle());

        $messageRepo = $this->getEm()->getRepository(AiMessage::class);
        $messages = $messageRepo->findBy(['conversation' => $conversation], ['id' => 'ASC']);
        $this->assertCount(2, $messages);

        $this->assertSame(AiMessageRole::USER, $messages[0]->getRole());
        $userEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $messages[0]]);
        $this->assertCount(1, $userEvents);
        $this->assertSame('Say hello', $userEvents[0]->getContent());

        $this->assertSame(AiMessageRole::ASSISTANT, $messages[1]->getRole());

        // merged into one
        $textEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $messages[1]]);
        $this->assertCount(1, $textEvents);
        $this->assertSame('Hello world', $textEvents[0]->getContent());
    }

    public function test_thinking_and_query_tool(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [
            new ThinkingDelta('reasoning about it...'),
            new ThinkingDelta('still thinking...'),
            new ThinkingComplete('reasoning about it... still thinking...', 'sig-123'),
            function (?\Closure $onQueryComplete) {
                $onQueryComplete?->__invoke('get_tags', ['limit' => 10], [['id' => 1, 'name' => 'News']]);
            },
            new TextDelta('just some text'),
        ]);

        $events = iterator_to_array($service->streamPrompt($blog, 'Think about it', $postVariant));

        $this->assertSame(
            ['conversation_created', 'thinking_chunk', 'thinking_chunk', 'event', 'text_chunk', 'done'],
            array_column($events, 'type'),
        );

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $assistantEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage]);

        $this->assertCount(3, $assistantEvents);

        $thinkingEvent = $assistantEvents[0];
        $this->assertSame(AiMessageEventType::THINKING, $thinkingEvent->getType());
        $this->assertSame('reasoning about it... still thinking...', $thinkingEvent->getContent());
        $this->assertSame('sig-123', $thinkingEvent->getSignature());

        $queryEvent = $assistantEvents[1];
        $this->assertSame(AiMessageEventType::QUERY, $queryEvent->getType());
        $this->assertSame('get_tags', $queryEvent->getToolName());
        $this->assertSame(['limit' => 10], $queryEvent->getToolInput());
        $this->assertSame([['id' => 1, 'name' => 'News']], $queryEvent->getToolOutput());

        // text persisted later
        $textEvent = $assistantEvents[2];
        $this->assertSame(AiMessageEventType::TEXT, $textEvent->getType());
        $this->assertSame('just some text', $textEvent->getContent());

    }

    public function test_persists_interleaved_events_in_order_with_text_merged_around_them(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [
            new TextDelta('Let me check. '),
            new ThinkingDelta('hmm'),
            new ThinkingComplete('hmm', null),
            new ToolCallStart('call-1', 'get_tags'),
            function (?\Closure $onQueryComplete) {
                $onQueryComplete?->__invoke('get_tags', ['limit' => 10], [['id' => 1, 'name' => 'News']]);
            },
            new ToolCallComplete([new ToolCall('call-1', 'get_tags', ['limit' => 10])]),
            new TextDelta('Found'),
            new TextDelta(' it.'),
        ]);

        iterator_to_array($service->streamPrompt($blog, 'Check tags', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $events = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage], ['id' => 'ASC']);

        $this->assertSame(
            [AiMessageEventType::TEXT, AiMessageEventType::THINKING, AiMessageEventType::QUERY, AiMessageEventType::TEXT],
            array_map(fn ($e) => $e->getType(), $events),
        );
        $this->assertSame('Let me check. ', $events[0]->getContent());
        $this->assertSame('hmm', $events[1]->getContent());
        $this->assertSame('get_tags', $events[2]->getToolName());
        $this->assertSame(['limit' => 10], $events[2]->getToolInput());
        $this->assertSame([['id' => 1, 'name' => 'News']], $events[2]->getToolOutput());
        // the two trailing TextDelta chunks are merged into one event
        $this->assertSame('Found it.', $events[3]->getContent());
    }

    public function test_yields_document_change_when_ops_were_made(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $documentOpsTool = new DocumentOpsTool(
            $blog,
            $this->getService(PostService::class),
        );
        $documentOpsTool->get($postVariant->getId());
        $documentOpsTool->replaceText($postVariant->getId(), 'p-1', 'foo', 'bar');

        $service = $this->buildService($postVariant, [new TextDelta('ok')], $documentOpsTool);

        $events = iterator_to_array($service->streamPrompt($blog, 'Change it', $postVariant));

        $this->assertSame(
            ['conversation_created', 'text_chunk', 'event', 'done'],
            array_column($events, 'type'),
        );

        $expectedContent = (string) json_encode($documentOpsTool->getFinalDocument($postVariant->getId())->toArray());
        $documentChangeEvent = $events[2]['event'];
        $this->assertInstanceOf(AiMessageEventObject::class, $documentChangeEvent);
        $this->assertSame($expectedContent, $documentChangeEvent->document_content);

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $events = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage]);

        $this->assertCount(2, $events);

        $documentChangeEvent = $events[1];
        $this->assertSame(AiMessageEventType::DOCUMENT_CHANGE, $documentChangeEvent->getType());
        $this->assertSame($expectedContent, $documentChangeEvent->getDocumentContent());
        $this->assertSame(AiMessageEventDocumentChangeStatus::PENDING, $documentChangeEvent->getDocumentChangeStatus());
        $this->assertSame(1, $documentChangeEvent->getDocumentChangeOpsCount());
        $this->assertSame($postVariant->getContentUnsavedVersion(), $documentChangeEvent->getPostVariantVersion());
    }

    public function test_continues_an_existing_conversation_with_prior_history_sent_to_the_agent(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $result = new class ([new TextDelta('Second reply')]) implements ResultInterface {
            private ?RawResultInterface $rawResult = null;

            /**
             * @param array<int, object> $deltas
             */
            public function __construct(private array $deltas)
            {
            }

            public function getContent(): iterable
            {
                return $this->deltas;
            }

            public function getRawResult(): ?RawResultInterface
            {
                return $this->rawResult;
            }

            public function setRawResult(RawResultInterface $rawResult): void
            {
                $this->rawResult = $rawResult;
            }

            public function getMetadata(): Metadata
            {
                return new Metadata();
            }
        };

        $documentOpsTool = new DocumentOpsTool(
            $blog,
            $this->getService(PostService::class),
        );
        $agentCallResult = new AgentCallResult($result, $documentOpsTool, AiModel::CLAUDE_OPUS_5);

        $fakeAiAgentService = new class ($agentCallResult) extends AiAgentService {
            /** @var array<int, ?MessageBag> */
            public array $capturedHistories = [];

            public function __construct(private AgentCallResult $agentCallResult)
            {
            }

            public function callAgent(
                Blog $blog,
                string $prompt,
                ?PostVariant $postVariant,
                ?MessageBag $history = null,
                ?\Closure $onQueryComplete = null,
            ): AgentCallResult {
                $this->capturedHistories[] = $history;
                return $this->agentCallResult;
            }
        };

        $service = new AiAgentConversationService(
            $this->getService(EntityManagerInterface::class),
            $fakeAiAgentService,
            new MessageHistoryBuilder($this->getService(EntityManagerInterface::class)),
            new NullLogger(),
        );

        // first turn starts a brand new conversation - no history to send
        $firstEvents = iterator_to_array($service->streamPrompt($blog, 'First message', $postVariant));
        $this->assertNull($fakeAiAgentService->capturedHistories[0]);

        $conversationStarted = $firstEvents[0];
        $this->assertSame('conversation_created', $conversationStarted['type']);
        $conversationObject = $conversationStarted['conversation'];
        $this->assertInstanceOf(AiConversationObject::class, $conversationObject);
        $conversationId = $conversationObject->id;

        $conversation = $this->getEm()->getRepository(AiConversation::class)->find($conversationId);
        $this->assertNotNull($conversation);

        // second turn continues the same conversation - the first turn's messages are sent as history
        iterator_to_array($service->streamPrompt($blog, 'Second message', $postVariant, $conversation));

        $secondHistory = $fakeAiAgentService->capturedHistories[1];
        $this->assertNotNull($secondHistory);
        $this->assertSame(2, $secondHistory->count());

        $messages = $this->getEm()->getRepository(AiMessage::class)
            ->findBy(['conversation' => $conversation], ['id' => 'ASC']);
        $this->assertCount(4, $messages);

        $firstUserTextEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $messages[0], 'type' => AiMessageEventType::TEXT]);
        $this->assertSame('First message', $firstUserTextEvents[0]->getContent());

        $secondUserTextEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $messages[2], 'type' => AiMessageEventType::TEXT]);
        $this->assertSame('Second message', $secondUserTextEvents[0]->getContent());
    }

    public function test_yields_error_event_and_persists_it_when_the_agent_call_fails(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [], throws: new \RuntimeException('provider exploded'));

        $events = iterator_to_array($service->streamPrompt($blog, 'Do something', $postVariant));

        $this->assertSame(
            ['conversation_created', 'event'],
            array_column($events, 'type'),
        );
        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $assistantEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage]);

        $this->assertCount(1, $assistantEvents);
        $errorEvent = $assistantEvents[0];
        $this->assertSame(AiMessageEventType::ERROR, $errorEvent->getType());
    }

    public function test_persists_token_usage_on_the_assistant_message(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $tokenUsage = new TokenUsage(promptTokens: 100, completionTokens: 50, totalTokens: 150);

        $service = $this->buildService(
            $postVariant,
            [new TextDelta('Hi')],
            metadata: new Metadata(['token_usage' => $tokenUsage]),
        );

        iterator_to_array($service->streamPrompt($blog, 'Say hi', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);
        $this->assertSame(100, $assistantMessage->getInputTokens());
        $this->assertSame(50, $assistantMessage->getOutputTokens());
        $this->assertSame(150, $assistantMessage->getTotalTokens());
    }

    public function test_persists_the_model_used_on_the_assistant_message(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [new TextDelta('Hi')], model: AiModel::CLAUDE_OPUS_5);

        iterator_to_array($service->streamPrompt($blog, 'Say hi', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);
        $this->assertSame('claude-opus-5', $assistantMessage->getModel());
    }

    public function test_persists_token_usd_cost_on_the_assistant_message(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        // claude-sonnet-5: $2.00/M input, $10.00/M output
        $tokenUsage = new TokenUsage(promptTokens: 500_000, completionTokens: 200_000, totalTokens: 700_000);

        $service = $this->buildService(
            $postVariant,
            [new TextDelta('Hi')],
            metadata: new Metadata(['token_usage' => $tokenUsage]),
            model: AiModel::CLAUDE_SONNET_5,
        );

        iterator_to_array($service->streamPrompt($blog, 'Say hi', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $this->assertSame(500_000, $assistantMessage->getInputTokens());
        $this->assertSame(200_000, $assistantMessage->getOutputTokens());
        $this->assertSame(700_000, $assistantMessage->getTotalTokens());

        $this->assertSame(1.0, $assistantMessage->getInputTokensUsdCost());
        $this->assertSame(2.0, $assistantMessage->getOutputTokensUsdCost());
        $this->assertSame(3.0, $assistantMessage->getTotalTokensUsdCost());
    }

    public function test_persists_fractional_cent_cost_for_small_token_counts(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $tokenUsage = new TokenUsage(promptTokens: 100, completionTokens: 50, totalTokens: 150);

        $service = $this->buildService(
            $postVariant,
            [new TextDelta('Hi')],
            metadata: new Metadata(['token_usage' => $tokenUsage]),
            model: AiModel::CLAUDE_SONNET_5,
        );

        iterator_to_array($service->streamPrompt($blog, 'Say hi', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);
        $this->assertSame(0.0002, $assistantMessage->getInputTokensUsdCost());
        $this->assertSame(0.0005, $assistantMessage->getOutputTokensUsdCost());
        $this->assertSame(0.0007, $assistantMessage->getTotalTokensUsdCost());
    }

}
