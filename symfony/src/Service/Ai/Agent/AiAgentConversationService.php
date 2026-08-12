<?php

namespace App\Service\Ai\Agent;

use App\Api\Console\Object\PostVariantObject;
use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageChunk;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageChunkType;
use App\Entity\Enum\AiMessageRole;
use App\Service\Ai\Agent\Event\AgentEvent;
use App\Service\Ai\Agent\Event\ThinkingDoneEvent;
use App\Service\Ai\Agent\Event\ThinkingStartedEvent;
use App\Service\Ai\Agent\Event\ToolCallEventFactory;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallStart;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AiAgentConversationService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private PostService $postService,
        private PostContentService $postContentService,
        private PermalinkService $permalinkService,
        private AiAgentService $aiAgentService,
        private ToolCallEventFactory $toolCallEventFactory,
    ) {}

    /**
     * @return iterable<array<string, mixed>> SSE-ready event payloads
     */
    public function streamPrompt(Blog $blog, string $prompt): iterable
    {
        $postVariant = $this->postService->getPostVariantByBlogAndId($blog, 117);

        if (!$postVariant) {
            throw new BadRequestHttpException('No published post found to run the agent on.');
        }

        $postVariantObject = new PostVariantObject(
            $postVariant,
            $postVariant->getPost(),
            $blog,
            $this->permalinkService,
            $this->postContentService
        );

        $conversation = new AiConversation();
        $conversation->setBlog($blog);
        $conversation->setCreatedAt($this->now());
        $conversation->setUpdatedAt($this->now());
        $this->em->persist($conversation);

        $userMessage = $this->createMessage($conversation, AiMessageRole::USER, $prompt);
        $this->createTextChunk($userMessage, $prompt);

        yield ['type' => 'post_variant', 'post_variant' => $postVariantObject];

        $agentCallResult = $this->aiAgentService->callForPost($postVariant, $prompt);

        $assistantMessage = $this->createMessage($conversation, AiMessageRole::ASSISTANT, '');
        $assistantText = '';
        $thinking = false;

        foreach ($agentCallResult->getResult()->getContent() as $delta) {
            $event = match (true) {
                $delta instanceof ThinkingDelta => ['type' => 'thinking', 'content' => $delta->getThinking()],
                $delta instanceof ThinkingComplete => ['type' => 'thinking_done'],
                $delta instanceof ToolCallStart => ['type' => 'tool_call', 'tool' => $delta->getName()],
                $delta instanceof ToolCallComplete => ['type' => 'tool_result', 'status' => 'done'],
                $delta instanceof TextDelta => ['type' => 'text', 'content' => (string) $delta],
                default => null,
            };

            if ($delta instanceof ThinkingDelta) {
                if (!$thinking) {
                    $thinking = true;
                    yield $this->toSseArray(new ThinkingStartedEvent());
                }
                $this->createTextChunk($assistantMessage, $delta->getThinking());
            }

            if ($delta instanceof ThinkingComplete) {
                $thinking = false;
                $this->createEventChunk($assistantMessage, new ThinkingDoneEvent());
            }

            if ($delta instanceof ToolCallComplete) {
                foreach ($delta->getToolCalls() as $toolCall) {
                    $toolCallEvent = $this->toolCallEventFactory->fromToolCall($toolCall);

                    if ($toolCallEvent !== null) {
                        $this->createEventChunk($assistantMessage, $toolCallEvent);
                    }
                }
            }

            if ($delta instanceof TextDelta) {
                $text = (string) $delta;
                $assistantText .= $text;
                $this->createTextChunk($assistantMessage, $text);
            }

            if ($event !== null) {
                yield $event;
            }
        }

        $assistantMessage->setContent($assistantText);
        $assistantMessage->setUpdatedAt($this->now());
        $this->em->flush();

        $documentOpsTool = $agentCallResult->getDocumentOpsTool();
        $fetchedDocument = $documentOpsTool->getCachedDocuments()[$postVariant->getId()] ?? null;

        if ($fetchedDocument !== null && count($fetchedDocument->getOps()) > 0) {
            $finalDocument = $documentOpsTool->getFinalDocument($postVariant->getId());

            yield [
                'type' => 'document_change',
                'post_variant_id' => $postVariant->getId(),
                'content' => json_encode($finalDocument->toArray()),
            ];
        }

        yield ['type' => 'done'];
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

    private function createTextChunk(AiMessage $message, string $content): void
    {
        if ($content === '') {
            return;
        }

        $chunk = new AiMessageChunk();
        $chunk->setMessage($message);
        $chunk->setType(AiMessageChunkType::TEXT);
        $chunk->setContent($content);
        $chunk->setCreatedAt($this->now());
        $chunk->setUpdatedAt($this->now());
        $this->em->persist($chunk);
        $this->em->flush();
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
