<?php

namespace App\Tests\Api\Console\Blog\Ai;

use App\Api\Console\Controller\AiController;
use App\Entity\AiConversation;
use App\Entity\Enum\AiMessageChunkType;
use App\Entity\Enum\AiMessageRole;
use App\Entity\Enum\UserStatus;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageChunkFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AiController::class)]
class AgentTest extends ApiTestCase
{
    public function test_fails_with_a_conversation_id_that_does_not_exist(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('POST', $blog, '/ai/agent', [
            'prompt' => 'hello',
            'conversation_id' => 999999,
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_with_a_conversation_id_belonging_to_another_blog(): void
    {
        $blog = BlogFactory::createOne();
        $otherBlog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $otherConversation = AiConversationFactory::createOneFor($otherBlog);

        $this->consoleBlogApi('POST', $blog, '/ai/agent', [
            'prompt' => 'hello',
            'conversation_id' => $otherConversation->getId(),
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_lists_conversations_for_the_blog_with_pagination(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        AiConversationFactory::createOneFor($blog)->setUpdatedAt(new \DateTimeImmutable('-2 days'));
        AiConversationFactory::createOneFor($blog)->setUpdatedAt(new \DateTimeImmutable('-1 days'));
        $this->getEm()->flush();

        // another blog's conversations must never show up
        AiConversationFactory::createOneFor(BlogFactory::createOne());

        $this->consoleBlogApi('GET', $blog, '/ai/conversations?limit=1&offset=0', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['conversations']);
        $this->assertCount(1, $json['conversations']);
        $this->assertTrue($json['has_more']);
        $this->assertIsArray($json['conversations'][0]);
        $this->assertArrayHasKey('title', $json['conversations'][0]);
    }

    public function test_gets_a_conversation_with_its_reconstructed_turns(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $conversation = AiConversationFactory::createOneFor($blog);
        $userMessage = AiMessageFactory::createOneFor($conversation);
        $userMessage->setRole(AiMessageRole::USER);
        $userMessage->setContent('Hi');

        $assistantMessage = AiMessageFactory::createOneFor($conversation);
        $assistantMessage->setRole(AiMessageRole::ASSISTANT);
        AiMessageChunkFactory::createOneFor($assistantMessage)
            ->setType(AiMessageChunkType::TEXT)
            ->setContent('Hello!');
        $this->getEm()->flush();

        $this->consoleBlogApi('GET', $blog, '/ai/conversation/' . $conversation->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($conversation->getId(), $json['id']);
        $this->assertIsArray($json['turns']);
        $this->assertCount(2, $json['turns']);
        $this->assertSame(['role' => 'user', 'content' => 'Hi'], $json['turns'][0]);
        $this->assertIsArray($json['turns'][1]);
        $this->assertSame([['type' => 'text', 'content' => 'Hello!']], $json['turns'][1]['events']);
    }

    public function test_fails_to_get_a_conversation_belonging_to_another_blog(): void
    {
        $blog = BlogFactory::createOne();
        $otherBlog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $otherConversation = AiConversationFactory::createOneFor($otherBlog);

        $this->consoleBlogApi('GET', $blog, '/ai/conversation/' . $otherConversation->getId(), user: $user);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_deletes_a_conversation(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $conversation = AiConversationFactory::createOneFor($blog);
        $conversationId = $conversation->getId();

        $this->consoleBlogApi('DELETE', $blog, '/ai/conversation/' . $conversationId, user: $user);

        $this->assertResponseIsSuccessful();
        $this->getEm()->clear();
        $this->assertNull($this->getEm()->getRepository(AiConversation::class)->find($conversationId));
    }

    public function test_fails_to_delete_a_conversation_belonging_to_another_blog(): void
    {
        $blog = BlogFactory::createOne();
        $otherBlog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $otherConversation = AiConversationFactory::createOneFor($otherBlog);

        $this->consoleBlogApi('DELETE', $blog, '/ai/conversation/' . $otherConversation->getId(), user: $user);

        $this->assertResponseStatusCodeSame(404);
    }
}
