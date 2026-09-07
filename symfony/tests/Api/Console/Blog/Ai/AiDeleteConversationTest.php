<?php

namespace App\Tests\Api\Console\Blog\Ai;

use App\Api\Console\Controller\AiController;
use App\Entity\AiConversation;
use App\Entity\Enum\UserStatus;
use App\Service\Ai\Agent\AiConversationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AiController::class)]
#[CoversClass(AiConversationService::class)]
class AiDeleteConversationTest extends ApiTestCase
{
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
