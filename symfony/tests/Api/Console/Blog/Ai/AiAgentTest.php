<?php

namespace App\Tests\Api\Console\Blog\Ai;

use App\Api\Console\Controller\AiController;
use App\Entity\AiConversation;
use App\Entity\Enum\UserStatus;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AiController::class)]
class AiAgentTest extends ApiTestCase
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
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertArrayHasKey('title', $json[0]);
    }

}
