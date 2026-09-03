<?php

namespace App\Tests\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\Enum\AiMessageRole;
use App\Service\Ai\Agent\AiConversationService;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageEventFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
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

    public function test_deleting_a_conversation_cascades_to_its_messages_and_events(): void
    {
        $blog = BlogFactory::createOne();
        $conversation = AiConversationFactory::createOneFor($blog);
        $conversationId = $conversation->getId();
        $message = AiMessageFactory::createOneFor($conversation);
        $messageId = $message->getId();
        $event = AiMessageEventFactory::createOneFor($message);
        $eventId = $event->getId();

        $this->service()->deleteConversation($conversation);

        $em = $this->getService(EntityManagerInterface::class);
        $em->clear();
        $this->assertNull($em->getRepository(AiConversation::class)->find($conversationId));
        $this->assertNull($em->getRepository(AiMessage::class)->find($messageId));
        $this->assertNull($em->getRepository(AiMessageEvent::class)->find($eventId));
    }

    public function test_gets_messages_for_a_conversation_with_their_events_in_order(): void
    {
        $blog = BlogFactory::createOne();
        $conversation = AiConversationFactory::createOneFor($blog);

        $userMessage = AiMessageFactory::createOneFor($conversation);
        $userMessage->setRole(AiMessageRole::USER);

        AiMessageEventFactory::createOneFor($userMessage)
            ->setType(AiMessageEventType::TEXT)
            ->setContent('Hello there');

        $assistantMessage = AiMessageFactory::createOneFor($conversation);
        $assistantMessage->setRole(AiMessageRole::ASSISTANT);

        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::THINKING)
            ->setContent('thinking...');

        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::QUERY)
            ->setToolName('get_tags')
            ->setToolInput(['limit' => 10])
            ->setToolOutput([['id' => 1, 'name' => 'News']]);

        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::TEXT)
            ->setContent('Hi!');

        $conversationId = $conversation->getId();

        $this->getEm()->flush();
        // force a fresh hydration of the messages and their events below - otherwise the
        // already-managed $assistantMessage instance keeps the empty Collection its
        // constructor set, since Doctrine won't repopulate an association already resident
        // in the identity map from a fetch-join alone
        $this->getEm()->clear();

        $conversation = $this->getEm()->getRepository(AiConversation::class)->find($conversationId);
        $this->assertNotNull($conversation);

        $messages = $this->service()->getMessages($conversation);

        $this->assertCount(2, $messages);
        $this->assertSame($userMessage->getId(), $messages[0]->getId());
        $this->assertSame($assistantMessage->getId(), $messages[1]->getId());

        $events = $messages[1]->getEvents()->toArray();
        $this->assertCount(3, $events);
        $this->assertSame(
            [AiMessageEventType::THINKING, AiMessageEventType::QUERY, AiMessageEventType::TEXT],
            array_map(fn ($e) => $e->getType(), $events),
        );
        $this->assertSame('thinking...', $events[0]->getContent());
        $this->assertSame('get_tags', $events[1]->getToolName());
        $this->assertSame(['limit' => 10], $events[1]->getToolInput());
        $this->assertSame([['id' => 1, 'name' => 'News']], $events[1]->getToolOutput());
        $this->assertSame('Hi!', $events[2]->getContent());
    }

    public function test_gets_involved_post_variants_for_a_conversation(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOneFor($blog);
        $conversation = AiConversationFactory::createOneFor($blog);

        $assistantMessage = AiMessageFactory::createOneFor($conversation);
        $assistantMessage->setRole(AiMessageRole::ASSISTANT);

        $variant1 = PostVariantFactory::createOneFor(PostFactory::createOneFor($blog), language: $language);
        $variant2 = PostVariantFactory::createOneFor(PostFactory::createOneFor($blog), language: $language);

        // two document_change events on the same variant - should only appear once
        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($variant1);
        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($variant1);
        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($variant2);

        // a query event referencing no post variant - should not blow up or appear
        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::QUERY)
            ->setToolName('get_tags');

        // another conversation's document_change on a third variant - must not leak in
        $otherConversation = AiConversationFactory::createOneFor($blog);
        $otherMessage = AiMessageFactory::createOneFor($otherConversation);
        $otherMessage->setRole(AiMessageRole::ASSISTANT);
        $variant3 = PostVariantFactory::createOneFor(PostFactory::createOneFor($blog), language: $language);
        AiMessageEventFactory::createOneFor($otherMessage)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($variant3);

        $this->getEm()->flush();

        $postVariants = $this->service()->getInvolvedPostVariants($conversation);

        $this->assertCount(2, $postVariants);
        $ids = array_map(fn ($v) => $v->getId(), $postVariants);
        $this->assertContains($variant1->getId(), $ids);
        $this->assertContains($variant2->getId(), $ids);
        $this->assertNotContains($variant3->getId(), $ids);
    }
}
