<?php

namespace App\Tests\Service\Ai\Agent\MessageHandler;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Service\Ai\Agent\Message\DeleteOldAiConversationsMessage;
use App\Service\Ai\Agent\MessageHandler\DeleteOldAiConversationsMessageHandler;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteOldAiConversationsMessageHandler::class)]
class DeleteOldAiConversationsMessageHandlerTest extends KernelTestCase
{
    public function test_deletes_conversations_inactive_for_over_30_days_and_cascades_to_messages(): void
    {
        $blog = BlogFactory::createOne();

        $oldConversation = AiConversationFactory::createOneFor($blog);
        $oldConversation->setUpdatedAt(new \DateTimeImmutable('-31 days'));
        $oldConversationId = $oldConversation->getId();
        $message = AiMessageFactory::createOneFor($oldConversation);
        $messageId = $message->getId();

        $recentConversation = AiConversationFactory::createOneFor($blog);
        $recentConversation->setUpdatedAt(new \DateTimeImmutable('-1 day'));
        $recentConversationId = $recentConversation->getId();

        $this->getEm()->flush();

        $handler = $this->getService(DeleteOldAiConversationsMessageHandler::class);
        $handler(new DeleteOldAiConversationsMessage());

        $this->getEm()->clear();

        $this->assertNull($this->getEm()->getRepository(AiConversation::class)->find($oldConversationId));
        $this->assertNull($this->getEm()->getRepository(AiMessage::class)->find($messageId));

        $this->assertNotNull($this->getEm()->getRepository(AiConversation::class)->find($recentConversationId));
    }
}
