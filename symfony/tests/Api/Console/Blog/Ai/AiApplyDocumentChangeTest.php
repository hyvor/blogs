<?php

namespace App\Tests\Api\Console\Blog\Ai;

use App\Api\Console\Controller\AiController;
use App\Entity\Enum\AiMessageEventDocumentChangeStatus;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariantStep;
use App\Service\Ai\Agent\AiConversationService;
use App\Service\Post\Document\DocumentService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageEventFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\PostVariantStepFactory;
use App\Tests\Fake\FakeHub;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(AiController::class)]
#[CoversClass(AiConversationService::class)]
#[CoversClass(DocumentService::class)]
class AiApplyDocumentChangeTest extends ApiTestCase
{

    public function test_applies_document_change(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'document_version' => 2,
            'words' => 100,
        ]);

        PostVariantStepFactory::createOneFor($variant, ['version' => 1]);
        PostVariantStepFactory::createOneFor($variant, ['version' => 2]);

        $conversation = AiConversationFactory::createOneFor($blog);
        $message = AiMessageFactory::createOneFor($conversation);
        $event = AiMessageEventFactory::createOneFor($message)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($variant)
            ->setDocumentChangeStatus(AiMessageEventDocumentChangeStatus::PENDING);

        $eventId = $event->getId();
        $this->getEm()->flush();

        $newContent = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"AI wrote this"}]}]}';

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/ai/document-changes/apply',
            [
                'event_id' => $eventId,
                'content' => $newContent,
                'agent_version' => 2,
            ],
            user: $user,
        );

        $this->assertResponseIsSuccessful();

        refresh($variant);
        $this->assertSame($newContent, $variant->getContentUnsaved());
        $this->assertSame(0, $variant->getContentUnsavedVersion());
        $this->assertSame(0, $variant->getDocumentVersion());
        $this->assertSame(100, $variant->getWords());

        $remainingSteps = $this->getEm()->getRepository(PostVariantStep::class)->findBy(['post_variant' => $variant]);
        $this->assertCount(0, $remainingSteps);

        refresh($event);
        $this->assertSame(AiMessageEventDocumentChangeStatus::REVIEWED, $event->getDocumentChangeStatus());

        $hub = $this->getService(FakeHub::class);
        $hub->assertPublished('document:' . $variant->getId());
        $updates = $hub->getPublished();
        $this->assertCount(1, $updates);
        $update = $updates[0];
        $this->assertSame(
            json_encode([
                'type' => 'new_document',
                'content' => $newContent,
            ], JSON_THROW_ON_ERROR),
            $update->getData()
        );
    }

    public function test_updates_word_count_for_draft_variant(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOneFor($post, [
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'document_version' => 1,
            'words' => 0,
        ]);

        $conversation = AiConversationFactory::createOneFor($blog);
        $message = AiMessageFactory::createOneFor($conversation);
        $event = AiMessageEventFactory::createOneFor($message)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($variant);

        $eventId = $event->getId();
        $this->getEm()->flush();

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/ai/document-changes/apply',
            [
                'event_id' => $eventId,
                'content' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hello, world!"}]}]}',
                'agent_version' => 1,
            ],
            user: $user,
        );

        $this->assertResponseIsSuccessful();

        refresh($variant);
        $this->assertSame(2, $variant->getWords());
    }

    public function test_fails_when_event_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/ai/document-changes/apply',
            [
                'event_id' => 999999,
                'content' => '{"type":"doc","content":[]}',
                'agent_version' => 0,
            ],
            user: $user,
        );

        $this->assertResponseFailed(400, 'Event not found');
    }

    public function test_fails_when_event_is_not_a_document_change_event(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $conversation = AiConversationFactory::createOneFor($blog);
        $message = AiMessageFactory::createOneFor($conversation);
        $event = AiMessageEventFactory::createOneFor($message)
            ->setType(AiMessageEventType::TEXT)
            ->setContent('Hello!');

        $eventId = $event->getId();
        $this->getEm()->flush();

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/ai/document-changes/apply',
            [
                'event_id' => $eventId,
                'content' => '{"type":"doc","content":[]}',
                'agent_version' => 0,
            ],
            user: $user,
        );

        $this->assertResponseFailed(400, 'Event is not a document change event');
    }

    public function test_fails_when_event_has_no_post_variant(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $conversation = AiConversationFactory::createOneFor($blog);
        $message = AiMessageFactory::createOneFor($conversation);
        $event = AiMessageEventFactory::createOneFor($message)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE);

        $eventId = $event->getId();
        $this->getEm()->flush();

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/ai/document-changes/apply',
            [
                'event_id' => $eventId,
                'content' => '{"type":"doc","content":[]}',
                'agent_version' => 0,
            ],
            user: $user,
        );

        $this->assertResponseFailed(400, 'Event does not have a post variant');
    }

    public function test_fails_with_conflict_on_version_mismatch(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'document_version' => 5,
        ]);

        $conversation = AiConversationFactory::createOneFor($blog);
        $message = AiMessageFactory::createOneFor($conversation);
        $event = AiMessageEventFactory::createOneFor($message)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($variant)
            ->setDocumentChangeStatus(AiMessageEventDocumentChangeStatus::PENDING);

        $eventId = $event->getId();
        $this->getEm()->flush();

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/ai/document-changes/apply',
            [
                'event_id' => $eventId,
                'content' => '{"type":"doc","content":[]}',
                'agent_version' => 2,
            ],
            user: $user,
        );

        $this->assertResponseFailed(409, 'PostVariant document_version is 5, but agent version is 2');

        refresh($variant);
        $this->assertSame(5, $variant->getDocumentVersion());
        $this->assertNull($variant->getContentUnsaved());

        refresh($event);
        $this->assertSame(AiMessageEventDocumentChangeStatus::PENDING, $event->getDocumentChangeStatus());
    }

    public function test_force_applies_despite_version_mismatch(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'document_version' => 5,
        ]);

        $conversation = AiConversationFactory::createOneFor($blog);
        $message = AiMessageFactory::createOneFor($conversation);
        $event = AiMessageEventFactory::createOneFor($message)
            ->setType(AiMessageEventType::DOCUMENT_CHANGE)
            ->setPostVariant($variant)
            ->setDocumentChangeStatus(AiMessageEventDocumentChangeStatus::PENDING);

        $eventId = $event->getId();
        $this->getEm()->flush();

        $newContent = '{"type":"doc","content":[]}';

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/ai/document-changes/apply',
            [
                'event_id' => $eventId,
                'content' => $newContent,
                'agent_version' => 2,
                'force' => true,
            ],
            user: $user,
        );

        $this->assertResponseIsSuccessful();

        refresh($variant);
        $this->assertSame($newContent, $variant->getContentUnsaved());
        $this->assertSame(0, $variant->getDocumentVersion());
        $this->assertSame(0, $variant->getContentUnsavedVersion());

        refresh($event);
        $this->assertSame(AiMessageEventDocumentChangeStatus::REVIEWED, $event->getDocumentChangeStatus());
    }

}
