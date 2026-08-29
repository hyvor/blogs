<?php

namespace App\Tests\Service\Post\MessageHandler;

use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Service\Post\Message\PublishScheduledPostVariantsMessage;
use App\Service\Post\MessageHandler\PublishScheduledPostVariantsMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PublishScheduledPostVariantsMessageHandler::class)]
class PublishScheduledPostVariantsMessageHandlerTest extends KernelTestCase
{
    public function test_publishes_only_scheduled_variants_whose_time_has_passed(): void
    {
        [$blog, ] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $draftPost = PostFactory::createOneFor($blog);
        $draft = PostVariantFactory::createOneFor($draftPost, [
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
        ]);

        $scheduledLaterPost = PostFactory::createOneFor($blog);
        $scheduledLater = PostVariantFactory::createOneFor($scheduledLaterPost, [
            'language' => $language,
            'status' => PostVariantStatus::SCHEDULED,
            'published_at' => new \DateTimeImmutable('+7 days'),
        ]);

        $scheduledDuePost = PostFactory::createOneFor($blog);
        $scheduledDue = PostVariantFactory::createOneFor($scheduledDuePost, [
            'language' => $language,
            'status' => PostVariantStatus::SCHEDULED,
            'published_at' => new \DateTimeImmutable('-1 hour'),
        ]);

        $publishedPost = PostFactory::createOneFor($blog);
        $published = PostVariantFactory::createOneFor($publishedPost, [
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'published_at' => new \DateTimeImmutable('-1 day'),
        ]);

        $handler = $this->getService(PublishScheduledPostVariantsMessageHandler::class);
        $handler(new PublishScheduledPostVariantsMessage());

        $this->getEm()->clear();

        $repository = $this->getEm()->getRepository(PostVariant::class);

        /** @var PostVariant $draftAfter */
        $draftAfter = $repository->find($draft->getId());
        $this->assertSame(PostVariantStatus::DRAFT, $draftAfter->getStatus());

        /** @var PostVariant $scheduledLaterAfter */
        $scheduledLaterAfter = $repository->find($scheduledLater->getId());
        $this->assertSame(PostVariantStatus::SCHEDULED, $scheduledLaterAfter->getStatus());

        /** @var PostVariant $scheduledDueAfter */
        $scheduledDueAfter = $repository->find($scheduledDue->getId());
        $this->assertSame(PostVariantStatus::PUBLISHED, $scheduledDueAfter->getStatus());

        /** @var PostVariant $publishedAfter */
        $publishedAfter = $repository->find($published->getId());
        $this->assertSame(PostVariantStatus::PUBLISHED, $publishedAfter->getStatus());
    }
}
