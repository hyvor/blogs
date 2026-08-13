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
use Symfony\AI\Platform\Metadata\Metadata;
use Symfony\AI\Platform\Result\RawResultInterface;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallStart;
use Symfony\AI\Platform\Result\ToolCall;

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
    private function buildService(PostVariant $postVariant, array $deltas, ?DocumentOpsTool $documentOpsTool = null): AiAgentConversationService
    {
        $result = new class ($deltas) implements ResultInterface {
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

        $documentOpsTool ??= new DocumentOpsTool(
            $postVariant->getPost()->getBlog(),
            $this->getService(PostService::class),
            $this->getService(PostContentService::class),
        );

        $agentCallResult = new AgentCallResult($result, $documentOpsTool);

        $fakeAiAgentService = new class ($agentCallResult) extends AiAgentService {
            public function __construct(private AgentCallResult $agentCallResult)
            {
            }

            public function callForPost(PostVariant $postVariant, string $prompt): AgentCallResult
            {
                return $this->agentCallResult;
            }
        };

        $fakePostService = new class ($postVariant) extends PostService {
            public function __construct(private PostVariant $postVariant)
            {
            }

            public function getPostVariantByBlogAndId(Blog $blog, int $id): PostVariant
            {
                return $this->postVariant;
            }
        };

        return new AiAgentConversationService(
            $this->getService(EntityManagerInterface::class),
            $fakePostService,
            $this->getService(PostContentService::class),
            $this->getService(PermalinkService::class),
            $fakeAiAgentService,
            new ToolCallEventFactory(),
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

        $events = iterator_to_array($service->streamPrompt($blog, 'Say hello', 42));

        $this->assertSame(
            ['post_variant', 'text', 'text', 'done'],
            array_column($events, 'type'),
        );

        $conversationRepo = $this->getEm()->getRepository(AiConversation::class);
        $conversations = $conversationRepo->findBy(['blog' => $blog]);
        $this->assertCount(1, $conversations);
        $conversation = $conversations[0];

        $this->assertSame(42, $conversation->getCreatedUserId());
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

        $events = iterator_to_array($service->streamPrompt($blog, 'Think about it', 42));

        // thinking_started is sent to the frontend, even though it's never persisted
        $this->assertSame(
            ['post_variant', 'thinking_started', 'thinking', 'thinking_done', 'text', 'done'],
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

        $events = iterator_to_array($service->streamPrompt($blog, 'Edit the post', 42));

        // the same typed event that gets persisted is now streamed to the frontend too,
        // instead of a generic 'tool_result' array
        $this->assertSame(
            ['post_variant', 'tool_call', 'post_variant_edit_suggested', 'done'],
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

        $events = iterator_to_array($service->streamPrompt($blog, 'Do something', 42));

        // still notifies the frontend via a generic fallback event, just doesn't persist it
        $this->assertSame(
            ['post_variant', 'tool_call', 'tool_result', 'done'],
            array_column($events, 'type'),
        );
        $this->assertSame(['type' => 'tool_result', 'tool' => 'some_unmapped_tool'], $events[2]);

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

        iterator_to_array($service->streamPrompt($blog, 'Read then continue', 42));

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

        $events = iterator_to_array($service->streamPrompt($blog, 'Change it', 42));

        $this->assertSame(
            ['post_variant', 'text', 'document_change', 'done'],
            array_column($events, 'type'),
        );
    }

}
