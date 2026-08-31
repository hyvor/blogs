<?php

namespace App\Tests\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageThinking;
use App\Entity\AiMessageToolCall;
use App\Entity\Enum\AiMessageRole;
use App\Service\Ai\Agent\AiConversationService;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\AiMessageThinkingFactory;
use App\Tests\Factory\AiMessageToolCallFactory;
use App\Tests\Factory\BlogFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[CoversClass(AiConversationService::class)]
class AiConversationServiceTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    private function service(): AiConversationService
    {
        return $this->getService(AiConversationService::class);
    }

    public function test_lists_conversations_for_a_blog_ordered_by_most_recently_active(): void
    {
        $blog = BlogFactory::createOne();
        $otherBlog = BlogFactory::createOne();

        $older = AiConversationFactory::createOneFor($blog);
        $older->setUpdatedAt(new \DateTimeImmutable('2025-01-01'));

        $newer = AiConversationFactory::createOneFor($blog);
        $newer->setUpdatedAt(new \DateTimeImmutable('2025-02-01'));

        AiConversationFactory::createOneFor($otherBlog);

        $this->getEm()->flush();

        $result = $this->service()->getConversationsForBlog($blog, 25, 0);

        $this->assertFalse($result['has_more']);
        $this->assertCount(2, $result['conversations']);
        $this->assertSame($newer->getId(), $result['conversations'][0]->getId());
        $this->assertSame($older->getId(), $result['conversations'][1]->getId());
    }

    public function test_paginates_conversations_and_reports_has_more(): void
    {
        $blog = BlogFactory::createOne();

        for ($i = 0; $i < 3; $i++) {
            AiConversationFactory::createOneFor($blog);
        }

        $firstPage = $this->service()->getConversationsForBlog($blog, 2, 0);
        $this->assertCount(2, $firstPage['conversations']);
        $this->assertTrue($firstPage['has_more']);

        $secondPage = $this->service()->getConversationsForBlog($blog, 2, 2);
        $this->assertCount(1, $secondPage['conversations']);
        $this->assertFalse($secondPage['has_more']);
    }

    public function test_deleting_a_conversation_cascades_to_its_messages_thinking_and_tool_calls(): void
    {
        $blog = BlogFactory::createOne();
        $conversation = AiConversationFactory::createOneFor($blog);
        $conversationId = $conversation->getId();
        $message = AiMessageFactory::createOneFor($conversation);
        $messageId = $message->getId();
        $thinking = AiMessageThinkingFactory::createOneFor($message);
        $thinkingId = $thinking->getId();
        $toolCall = AiMessageToolCallFactory::createOneFor($message);
        $toolCallId = $toolCall->getId();

        $this->service()->deleteConversation($conversation);

        $em = $this->getService(EntityManagerInterface::class);
        $em->clear();
        $this->assertNull($em->getRepository(AiConversation::class)->find($conversationId));
        $this->assertNull($em->getRepository(AiMessage::class)->find($messageId));
        $this->assertNull($em->getRepository(AiMessageThinking::class)->find($thinkingId));
        $this->assertNull($em->getRepository(AiMessageToolCall::class)->find($toolCallId));
    }

    public function test_reconstructs_turns_from_messages_thinking_and_tool_calls(): void
    {
        $blog = BlogFactory::createOne();
        $conversation = AiConversationFactory::createOneFor($blog);

        $userMessage = AiMessageFactory::createOneFor($conversation);
        $userMessage->setRole(AiMessageRole::USER);
        $userMessage->setContent('Hello there');

        $assistantMessage = AiMessageFactory::createOneFor($conversation);
        $assistantMessage->setRole(AiMessageRole::ASSISTANT);
        $assistantMessage->setContent('Hi!');
        $assistantMessage->setModel('claude-sonnet-5');
        $assistantMessage->setInputTokens(10);
        $assistantMessage->setOutputTokens(20);
        $assistantMessage->setTotalTokens(30);

        AiMessageThinkingFactory::createOneFor($assistantMessage)->setSummary('thinking...');

        AiMessageToolCallFactory::createOneFor($assistantMessage)
            ->setToolName('document_get')
            ->setArguments(['postVariantId' => 42]);

        $this->getEm()->flush();

        $turns = $this->service()->getTurns($conversation);

        $this->assertCount(2, $turns);
        $this->assertSame(['role' => 'user', 'content' => 'Hello there'], $turns[0]);

        $this->assertSame('assistant', $turns[1]['role']);
        $this->assertSame('claude-sonnet-5', $turns[1]['model']);
        $this->assertSame(10, $turns[1]['input_tokens']);
        $this->assertSame(20, $turns[1]['output_tokens']);
        $this->assertSame(30, $turns[1]['total_tokens']);

        $events = $turns[1]['events'];
        $this->assertIsArray($events);
        $this->assertSame(
            ['thinking_started', 'thinking', 'thinking_done', 'post_variant_read', 'text'],
            array_column($events, 'type'),
        );
        $this->assertIsArray($events[1]);
        $this->assertSame('thinking...', $events[1]['content']);
        $this->assertIsArray($events[3]);
        $this->assertSame(42, $events[3]['post_variant_id']);
        $this->assertIsArray($events[4]);
        $this->assertSame('Hi!', $events[4]['content']);
    }
}
