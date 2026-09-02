<?php

namespace App\Tests\Api\Console\Blog\Ai;

use App\Api\Console\Controller\AiController;
use App\Entity\Enum\AiMessageEventDocumentChangeStatus;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\Enum\AiMessageRole;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserStatus;
use App\Service\Ai\Agent\AiConversationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageEventFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AiController::class)]
#[CoversClass(AiConversationService::class)]
class AiGetConversationTest extends ApiTestCase
{


    public function test_gets_a_conversation_with_its_messages_and_events(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $conversation = AiConversationFactory::createOneFor($blog);
        $conversation->setTitle('Hi there');

        $userMessage = AiMessageFactory::createOneFor($conversation);
        $userMessage->setRole(AiMessageRole::USER);

        AiMessageEventFactory::createOneFor($userMessage)
            ->setType(AiMessageEventType::TEXT)
            ->setContent('Hi');

        $assistantMessage = AiMessageFactory::createOneFor($conversation);
        $assistantMessage->setRole(AiMessageRole::ASSISTANT);

        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::TEXT)
            ->setContent('Hello!');

        $language = LanguageFactory::createOneFor($blog);
        $postVariant = PostVariantFactory::createOneFor(
            PostFactory::createOneFor($blog),
            [
                'status' => PostVariantStatus::PUBLISHED,
                'title' => 'My published post',
            ],
            language: $language,
        );

        AiMessageEventFactory::createOneFor($assistantMessage)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($postVariant)
            ->setDocumentContent('{"type":"doc","content":[]}')
            ->setDocumentChangeStatus(AiMessageEventDocumentChangeStatus::PENDING)
            ->setDocumentChangeOpsCount(2)
            ->setPostVariantVersion(5);

        $conversationId = $conversation->getId();

        $this->getEm()->flush();
        // force a fresh hydration on the request below - otherwise the already-managed
        // $assistantMessage instance keeps the empty events Collection its constructor set,
        // since Doctrine won't repopulate an association already resident in the identity map
        $this->getEm()->clear();

        $this->consoleBlogApi('GET', $blog, '/ai/conversation/' . $conversationId, user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['conversation']);
        $this->assertSame($conversation->getId(), $json['conversation']['id']);
        $this->assertSame('Hi there', $json['conversation']['title']);

        $this->assertIsArray($json['messages']);
        $this->assertCount(2, $json['messages']);

        $userMessageJson = $json['messages'][0];
        $this->assertIsArray($userMessageJson);
        $this->assertSame('user', $userMessageJson['role']);
        $this->assertSame('Hi', $userMessageJson['content']);
        $this->assertIsArray($userMessageJson['events']);
        $this->assertCount(1, $userMessageJson['events']);

        $assistantMessageJson = $json['messages'][1];
        $this->assertIsArray($assistantMessageJson);
        $this->assertSame('assistant', $assistantMessageJson['role']);
        $this->assertSame('Hello!', $assistantMessageJson['content']);
        $this->assertIsArray($assistantMessageJson['events']);
        $this->assertCount(2, $assistantMessageJson['events']);

        $textEventJson = $assistantMessageJson['events'][0];
        $this->assertIsArray($textEventJson);
        $this->assertSame('text', $textEventJson['type']);
        $this->assertSame('Hello!', $textEventJson['content']);

        $documentChangeEventJson = $assistantMessageJson['events'][1];
        $this->assertIsArray($documentChangeEventJson);
        $this->assertSame('document_change', $documentChangeEventJson['type']);
        $this->assertSame($postVariant->getId(), $documentChangeEventJson['post_variant_id']);
        $this->assertSame('{"type":"doc","content":[]}', $documentChangeEventJson['document_content']);
        $this->assertSame('pending', $documentChangeEventJson['document_change_status']);
        $this->assertSame(2, $documentChangeEventJson['document_change_ops_count']);
        $this->assertSame(5, $documentChangeEventJson['post_variant_version']);

        $this->assertIsArray($json['post_variants']);
        $this->assertCount(1, $json['post_variants']);
        $postVariantJson = $json['post_variants'][0];
        $this->assertIsArray($postVariantJson);
        $this->assertSame($postVariant->getId(), $postVariantJson['id']);
        $this->assertSame('My published post', $postVariantJson['title']);
        $this->assertSame('published', $postVariantJson['status']);
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
