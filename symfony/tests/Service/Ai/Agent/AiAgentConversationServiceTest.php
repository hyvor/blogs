<?php

namespace App\Tests\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageEvent;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageEventDocumentChangeStatus;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\Enum\AiMessageRole;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\AiAgentConversationService;
use App\Service\Ai\Agent\AiAgentHistoryService;
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
class AiAgentConversationServiceTest extends KernelTestCase
{

    private function createPostVariant(): PostVariant
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOneFor($blog);
        $post = PostFactory::createOneFor($blog);

        return PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
        ]);
    }

    /**
     *
     * @param array<int, object|\Closure> $deltas
     */
    private function buildService(
        PostVariant $postVariant,
        array $deltas,
        ?DocumentOpsTool $documentOpsTool = null,
        ?Metadata $metadata = null,
        ?\Throwable $throws = null,
        string $model = 'test-model',
    ): AiAgentConversationService {
        $documentOpsTool ??= new DocumentOpsTool(
            $postVariant->getPost()->getBlog(),
            $this->getService(PostService::class),
        );

        $fakeAiAgentService = new class ($deltas, $metadata ?? new Metadata(), $documentOpsTool, $throws) extends AiAgentService {
            /**
             * @param array<int, object|\Closure> $deltas
             */
            public function __construct(
                private array $deltas,
                private Metadata $metadata,
                private DocumentOpsTool $documentOpsTool,
                private ?\Throwable $throws,
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

                return new AgentCallResult($result, $this->documentOpsTool, AiModel::CLAUDE_OPUS_5);
            }
        };

        return new AiAgentConversationService(
            $this->getService(EntityManagerInterface::class),
            $fakeAiAgentService,
            new AiAgentHistoryService($this->getService(EntityManagerInterface::class)),
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
            ['conversation_created', 'text', 'text', 'done'],
            array_column($events, 'type'),
        );
        $this->assertSame('Say hello', $events[0]['title']);

        $conversationRepo = $this->getEm()->getRepository(AiConversation::class);
        $conversations = $conversationRepo->findBy(['blog' => $blog]);
        $this->assertCount(1, $conversations);
        $conversation = $conversations[0];

        $this->assertSame('Say hello', $conversation->getTitle());

        $messageRepo = $this->getEm()->getRepository(AiMessage::class);
        $messages = $messageRepo->findBy(['conversation' => $conversation], ['id' => 'ASC']);
        $this->assertCount(2, $messages);

        $this->assertSame(AiMessageRole::USER, $messages[0]->getRole());
        $userTextEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $messages[0], 'type' => AiMessageEventType::TEXT]);
        $this->assertCount(1, $userTextEvents);
        $this->assertSame('Say hello', $userTextEvents[0]->getContent());

        $this->assertSame(AiMessageRole::ASSISTANT, $messages[1]->getRole());

        // consecutive TextDelta chunks are merged into a single 'text' event, saved once the
        // stream ends (not one row per delta)
        $textEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $messages[1], 'type' => AiMessageEventType::TEXT]);
        $this->assertCount(1, $textEvents);
        $this->assertSame('Hello world', $textEvents[0]->getContent());
    }

    public function test_persists_a_thinking_row_on_thinking_complete(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [
            new ThinkingDelta('reasoning about it...'),
            new ThinkingComplete('reasoning about it...', 'sig-123'),
            new TextDelta('done'),
        ]);

        $events = iterator_to_array($service->streamPrompt($blog, 'Think about it', $postVariant));

        // the per-delta thinking stream is sent to the frontend live, even though only the
        // completed summary is persisted
        // note: a 'thinking_started' event is not actually emitted here - ThinkingStartedEvent
        // is currently unused dead code in streamPrompt (pre-existing, unrelated to this test)
        $this->assertSame(
            ['conversation_created', 'thinking', 'thinking_done', 'text', 'done'],
            array_column($events, 'type'),
        );

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $thinkingRows = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage, 'type' => AiMessageEventType::THINKING]);

        $this->assertCount(1, $thinkingRows);
        $this->assertSame('reasoning about it...', $thinkingRows[0]->getContent());
        $this->assertSame('sig-123', $thinkingRows[0]->getSignature());
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
            // simulates the query tool's onQueryComplete callback firing partway through the
            // stream, as it would inside the real QueryTool::getTags() call
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

    public function test_document_ops_tool_calls_are_not_persisted_individually(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $arguments = ['postVariantId' => $postVariant->getId(), 'nodeId' => 'p-1', 'contentMarkdown' => 'new text'];

        $service = $this->buildService($postVariant, [
            new ToolCallStart('call-1', 'document_replace'),
            new ToolCallComplete([new ToolCall('call-1', 'document_replace', $arguments)]),
        ]);

        $events = iterator_to_array($service->streamPrompt($blog, 'Edit the post', $postVariant));

        // the live SSE stream still notifies the frontend as before
        $this->assertSame(
            ['conversation_created', 'tool_call', 'post_variant_edit_suggested', 'done'],
            array_column($events, 'type'),
        );

        $expectedPayload = [
            'type' => 'post_variant_edit_suggested',
            'post_variant_id' => $postVariant->getId(),
            'operation' => 'replace',
            'arguments' => $arguments,
        ];

        $this->assertSame($expectedPayload, $events[2]);

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        // document-ops tool calls are no longer persisted one row per call - only the final
        // document_change (once the stream ends) is - see test_yields_document_change_when_ops_were_made
        $this->assertCount(0, $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage]));
    }

    public function test_unmapped_tool_calls_are_not_persisted(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [
            new ToolCallStart('call-1', 'some_unmapped_tool'),
            new ToolCallComplete([new ToolCall('call-1', 'some_unmapped_tool', ['foo' => 'bar'])]),
        ]);

        $events = iterator_to_array($service->streamPrompt($blog, 'Do something', $postVariant));

        // still notifies the frontend via a generic fallback event
        $this->assertSame(
            ['conversation_created', 'tool_call', 'tool_result', 'done'],
            array_column($events, 'type'),
        );
        $this->assertSame(['type' => 'tool_result', 'tool' => 'some_unmapped_tool'], $events[2]);

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        // a tool call with no query/document-ops handling is not persisted at all
        $this->assertCount(0, $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage]));
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
            ['conversation_created', 'text', 'document_change', 'done'],
            array_column($events, 'type'),
        );

        $expectedContent = (string) json_encode($documentOpsTool->getFinalDocument($postVariant->getId())->toArray());
        $this->assertSame($expectedContent, $events[2]['content']);

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $documentChangeEvents = $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage, 'type' => AiMessageEventType::DOCUMENT_CHANGE]);

        $this->assertCount(1, $documentChangeEvents);
        $this->assertSame($postVariant->getId(), $documentChangeEvents[0]->getPostVariant()?->getId());
        $this->assertSame($expectedContent, $documentChangeEvents[0]->getDocumentContent());
        $this->assertSame(AiMessageEventDocumentChangeStatus::PENDING, $documentChangeEvents[0]->getDocumentChangeStatus());
        $this->assertSame(1, $documentChangeEvents[0]->getDocumentChangeOpsCount());
        $this->assertSame($postVariant->getContentUnsavedVersion(), $documentChangeEvents[0]->getPostVariantVersion());
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
            new AiAgentHistoryService($this->getService(EntityManagerInterface::class)),
            new NullLogger(),
        );

        // first turn starts a brand new conversation - no history to send
        $firstEvents = iterator_to_array($service->streamPrompt($blog, 'First message', $postVariant));
        $this->assertNull($fakeAiAgentService->capturedHistories[0]);

        $conversationStarted = $firstEvents[0];
        $this->assertSame('conversation_created', $conversationStarted['type']);
        $conversationId = $conversationStarted['conversation_id'];

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
            ['conversation_created', 'error', 'done'],
            array_column($events, 'type'),
        );
        $this->assertSame(
            'The AI assistant ran into a problem and could not finish this request. Please try again.',
            $events[1]['message'],
        );

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        // the error itself is not persisted anywhere - just sent to the frontend and logged
        $this->assertCount(0, $this->getEm()->getRepository(AiMessageEvent::class)
            ->findBy(['ai_message' => $assistantMessage]));
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

        $service = $this->buildService($postVariant, [new TextDelta('Hi')], model: 'claude-sonnet-5');

        iterator_to_array($service->streamPrompt($blog, 'Say hi', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);
        $this->assertSame('claude-sonnet-5', $assistantMessage->getModel());
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
            model: 'claude-sonnet-5',
        );

        iterator_to_array($service->streamPrompt($blog, 'Say hi', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);
        $this->assertSame(100.0, $assistantMessage->getInputTokensUsdCost());
        $this->assertSame(200.0, $assistantMessage->getOutputTokensUsdCost());
        $this->assertSame(300.0, $assistantMessage->getTotalTokensUsdCost());
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
            model: 'claude-sonnet-5',
        );

        iterator_to_array($service->streamPrompt($blog, 'Say hi', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);
        $this->assertSame(0.00002, $assistantMessage->getInputTokensUsdCost());
        $this->assertSame(0.00005, $assistantMessage->getOutputTokensUsdCost());
        $this->assertSame(0.00007, $assistantMessage->getTotalTokensUsdCost());
    }

    public function test_does_not_persist_token_usd_cost_for_an_unknown_model(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $tokenUsage = new TokenUsage(promptTokens: 100, completionTokens: 50, totalTokens: 150);

        $service = $this->buildService(
            $postVariant,
            [new TextDelta('Hi')],
            metadata: new Metadata(['token_usage' => $tokenUsage]),
            model: 'test-model',
        );

        iterator_to_array($service->streamPrompt($blog, 'Say hi', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);
        $this->assertNull($assistantMessage->getTotalTokensUsdCost());
    }

}
