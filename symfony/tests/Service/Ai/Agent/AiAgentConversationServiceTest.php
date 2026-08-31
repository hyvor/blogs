<?php

namespace App\Tests\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageChunk;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageChunkType;
use App\Entity\Enum\AiMessageRole;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\AiAgentConversationService;
use App\Service\Ai\Agent\AiAgentHistoryService;
use App\Service\Ai\Agent\AiAgentService;
use App\Service\Ai\Agent\Tool\AgentCallResult;
use App\Service\Ai\Agent\Tool\DocumentOps\DocumentOpsTool;
use App\Service\Ai\Agent\Event\ToolCallEventFactory;
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
     * @param array<int, object> $deltas
     */
    private function buildService(
        PostVariant $postVariant,
        array $deltas,
        ?DocumentOpsTool $documentOpsTool = null,
        ?Metadata $metadata = null,
        ?\Throwable $throws = null,
    ): AiAgentConversationService {
        $result = new class ($deltas, $metadata ?? new Metadata()) implements ResultInterface {
            private ?RawResultInterface $rawResult = null;

            /**
             * @param array<int, object> $deltas
             */
            public function __construct(private array $deltas, private Metadata $metadata)
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
                return $this->metadata;
            }
        };

        $documentOpsTool ??= new DocumentOpsTool(
            $postVariant->getPost()->getBlog(),
            $this->getService(PostService::class),
            $this->getService(PostContentService::class),
        );

        $agentCallResult = new AgentCallResult($result, $documentOpsTool);

        $fakeAiAgentService = new class ($agentCallResult, $throws) extends AiAgentService {
            public function __construct(private AgentCallResult $agentCallResult, private ?\Throwable $throws)
            {
            }

            public function callAgent(
                Blog $blog,
                string $prompt,
                ?PostVariant $postVariant,
                ?MessageBag $history = null,
            ): AgentCallResult {
                if ($this->throws !== null) {
                    throw $this->throws;
                }

                return $this->agentCallResult;
            }
        };

        return new AiAgentConversationService(
            $this->getService(EntityManagerInterface::class),
            $fakeAiAgentService,
            new ToolCallEventFactory(),
            new AiAgentHistoryService($this->getService(EntityManagerInterface::class)),
            $this->getService(PermalinkService::class),
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
            ['conversation_started', 'post_variant', 'text', 'text', 'done'],
            array_column($events, 'type'),
        );

        $conversationRepo = $this->getEm()->getRepository(AiConversation::class);
        $conversations = $conversationRepo->findBy(['blog' => $blog]);
        $this->assertCount(1, $conversations);
        $conversation = $conversations[0];

        $this->assertSame('Say hello', $conversation->getTitle());

        $messageRepo = $this->getEm()->getRepository(AiMessage::class);
        $messages = $messageRepo->findBy(['conversation' => $conversation], ['id' => 'ASC']);
        $this->assertCount(2, $messages);

        $this->assertSame(AiMessageRole::USER, $messages[0]->getRole());
        $this->assertSame('Say hello', $messages[0]->getContent());

        $this->assertSame(AiMessageRole::ASSISTANT, $messages[1]->getRole());
        $this->assertSame('Hello world', $messages[1]->getContent());

        $chunkRepo = $this->getEm()->getRepository(AiMessageChunk::class);

        $userChunks = $chunkRepo->findBy(['message' => $messages[0]]);
        $this->assertCount(1, $userChunks);
        $this->assertSame(AiMessageChunkType::TEXT, $userChunks[0]->getType());
        $this->assertSame('Say hello', $userChunks[0]->getContent());

        // consecutive TextDelta chunks are merged into a single row
        $assistantChunks = $chunkRepo->findBy(['message' => $messages[1]], ['id' => 'ASC']);
        $this->assertCount(1, $assistantChunks);
        $this->assertSame(AiMessageChunkType::TEXT, $assistantChunks[0]->getType());
        $this->assertSame('Hello world', $assistantChunks[0]->getContent());
    }

    public function test_persists_thinking_done_event_but_not_thinking_started(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [
            new ThinkingDelta('reasoning about it...'),
            new ThinkingComplete('reasoning about it...'),
            new TextDelta('done'),
        ]);

        $events = iterator_to_array($service->streamPrompt($blog, 'Think about it', $postVariant));

        // thinking_started is sent to the frontend, even though it's never persisted
        $this->assertSame(
            ['conversation_started', 'post_variant', 'thinking_started', 'thinking', 'thinking_done', 'text', 'done'],
            array_column($events, 'type'),
        );

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $chunks = $this->getEm()->getRepository(AiMessageChunk::class)
            ->findBy(['message' => $assistantMessage], ['id' => 'ASC']);

        $this->assertCount(3, $chunks);

        $this->assertSame(AiMessageChunkType::THINKING, $chunks[0]->getType());
        $this->assertSame('reasoning about it...', $chunks[0]->getContent());

        $this->assertSame(AiMessageChunkType::EVENT, $chunks[1]->getType());
        $this->assertSame('thinking_done', $chunks[1]->getContent());
        $this->assertSame(['type' => 'thinking_done'], $chunks[1]->getEventPayload());

        $this->assertSame(AiMessageChunkType::TEXT, $chunks[2]->getType());
        $this->assertSame('done', $chunks[2]->getContent());
    }

    public function test_persists_typed_event_for_document_edit_tool_call(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $arguments = ['postVariantId' => $postVariant->getId(), 'nodeId' => 'p-1', 'contentMarkdown' => 'new text'];

        $service = $this->buildService($postVariant, [
            new ToolCallStart('call-1', 'document_replace'),
            new ToolCallComplete([new ToolCall('call-1', 'document_replace', $arguments)]),
        ]);

        $events = iterator_to_array($service->streamPrompt($blog, 'Edit the post', $postVariant));

        // the same typed event that gets persisted is now streamed to the frontend too,
        // instead of a generic 'tool_result' array
        $this->assertSame(
            ['conversation_started', 'post_variant', 'tool_call', 'post_variant_edit_suggested', 'done'],
            array_column($events, 'type'),
        );

        $expectedPayload = [
            'type' => 'post_variant_edit_suggested',
            'post_variant_id' => $postVariant->getId(),
            'operation' => 'replace',
            'arguments' => $arguments,
        ];

        $this->assertSame($expectedPayload, $events[3]);

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $chunks = $this->getEm()->getRepository(AiMessageChunk::class)
            ->findBy(['message' => $assistantMessage]);

        $this->assertCount(1, $chunks);
        $this->assertSame(AiMessageChunkType::EVENT, $chunks[0]->getType());
        $this->assertSame('post_variant_edit_suggested', $chunks[0]->getContent());
        $this->assertSame($expectedPayload, $chunks[0]->getEventPayload());
    }

    public function test_ignores_tool_calls_with_no_matching_event(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [
            new ToolCallStart('call-1', 'some_unmapped_tool'),
            new ToolCallComplete([new ToolCall('call-1', 'some_unmapped_tool', [])]),
        ]);

        $events = iterator_to_array($service->streamPrompt($blog, 'Do something', $postVariant));

        // still notifies the frontend via a generic fallback event, just doesn't persist it
        $this->assertSame(
            ['conversation_started', 'post_variant', 'tool_call', 'tool_result', 'done'],
            array_column($events, 'type'),
        );
        $this->assertSame(['type' => 'tool_result', 'tool' => 'some_unmapped_tool'], $events[3]);

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $chunks = $this->getEm()->getRepository(AiMessageChunk::class)
            ->findBy(['message' => $assistantMessage]);

        $this->assertCount(0, $chunks);
    }

    public function test_starts_a_new_chunk_after_a_break_instead_of_merging(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $arguments = ['postVariantId' => $postVariant->getId()];

        $service = $this->buildService($postVariant, [
            new TextDelta('Before.'),
            new ToolCallStart('call-1', 'document_get'),
            new ToolCallComplete([new ToolCall('call-1', 'document_get', $arguments)]),
            new TextDelta('After.'),
        ]);

        iterator_to_array($service->streamPrompt($blog, 'Read then continue', $postVariant));

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $chunks = $this->getEm()->getRepository(AiMessageChunk::class)
            ->findBy(['message' => $assistantMessage], ['id' => 'ASC']);

        $this->assertCount(3, $chunks);
        $this->assertSame(AiMessageChunkType::TEXT, $chunks[0]->getType());
        $this->assertSame('Before.', $chunks[0]->getContent());
        $this->assertSame(AiMessageChunkType::EVENT, $chunks[1]->getType());
        $this->assertSame('post_variant_read', $chunks[1]->getContent());
        $this->assertSame(AiMessageChunkType::TEXT, $chunks[2]->getType());
        $this->assertSame('After.', $chunks[2]->getContent());
    }

    public function test_yields_document_change_when_ops_were_made(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $documentOpsTool = new DocumentOpsTool(
            $blog,
            $this->getService(PostService::class),
            $this->getService(PostContentService::class),
        );
        $documentOpsTool->get($postVariant->getId());
        $documentOpsTool->replaceText($postVariant->getId(), 'p-1', 'foo', 'bar');

        $service = $this->buildService($postVariant, [new TextDelta('ok')], $documentOpsTool);

        $events = iterator_to_array($service->streamPrompt($blog, 'Change it', $postVariant));

        $this->assertSame(
            ['conversation_started', 'post_variant', 'text', 'document_change', 'done'],
            array_column($events, 'type'),
        );
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
            $this->getService(PostContentService::class),
        );
        $agentCallResult = new AgentCallResult($result, $documentOpsTool);

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
            ): AgentCallResult {
                $this->capturedHistories[] = $history;
                return $this->agentCallResult;
            }
        };

        $service = new AiAgentConversationService(
            $this->getService(EntityManagerInterface::class),
            $fakeAiAgentService,
            new ToolCallEventFactory(),
            new AiAgentHistoryService($this->getService(EntityManagerInterface::class)),
            $this->getService(PermalinkService::class),
            new NullLogger(),
        );

        // first turn starts a brand new conversation - no history to send
        $firstEvents = iterator_to_array($service->streamPrompt($blog, 'First message', $postVariant));
        $this->assertNull($fakeAiAgentService->capturedHistories[0]);

        $conversationStarted = $firstEvents[0];
        $this->assertSame('conversation_started', $conversationStarted['type']);
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
        $this->assertSame('First message', $messages[0]->getContent());
        $this->assertSame('Second message', $messages[2]->getContent());
    }

    public function test_yields_error_event_and_persists_it_when_the_agent_call_fails(): void
    {
        $postVariant = $this->createPostVariant();
        $blog = $postVariant->getPost()->getBlog();

        $service = $this->buildService($postVariant, [], throws: new \RuntimeException('provider exploded'));

        $events = iterator_to_array($service->streamPrompt($blog, 'Do something', $postVariant));

        $this->assertSame(
            ['conversation_started', 'post_variant', 'error', 'done'],
            array_column($events, 'type'),
        );
        $this->assertSame(
            'The AI assistant ran into a problem and could not finish this request. Please try again.',
            $events[2]['message'],
        );

        $assistantMessage = $this->getEm()->getRepository(AiMessage::class)
            ->findOneBy(['role' => AiMessageRole::ASSISTANT]);
        $this->assertNotNull($assistantMessage);

        $chunks = $this->getEm()->getRepository(AiMessageChunk::class)
            ->findBy(['message' => $assistantMessage]);
        $this->assertCount(1, $chunks);
        $this->assertSame(AiMessageChunkType::EVENT, $chunks[0]->getType());
        $this->assertSame('error', $chunks[0]->getContent());
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
        $this->assertSame(100, $assistantMessage->getPromptTokens());
        $this->assertSame(50, $assistantMessage->getCompletionTokens());
        $this->assertSame(150, $assistantMessage->getTotalTokens());
    }

}
