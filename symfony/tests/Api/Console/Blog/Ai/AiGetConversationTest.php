<?php

namespace App\Tests\Api\Console\Blog\Ai;

use App\Api\Console\Controller\AiController;
use App\Entity\Enum\AiMessageRole;
use App\Entity\Enum\UserStatus;
use App\Service\Ai\Agent\AiConversationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AiController::class)]
#[CoversClass(AiConversationService::class)]
class AiGetConversationTest extends ApiTestCase
{


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
        $assistantMessage->setContent('Hello!');
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

}
