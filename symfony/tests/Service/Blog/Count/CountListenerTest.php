<?php

namespace App\Tests\Service\Blog\Count;

use App\Entity\Blog;
use App\Service\App\Messenger\MessageTransport;
use App\Service\Blog\Count\CountListener;
use App\Service\Blog\Count\CountType;
use App\Service\Blog\Count\RecalculateCountMessage;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Post\Event\PostCreatedEvent;
use App\Service\Post\Event\PostDeletedEvent;
use App\Service\Post\Event\PostUpdatedEvent;
use App\Service\Post\Event\PostVariantPublishedEvent;
use App\Service\Post\Event\PostVariantUnpublishedEvent;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CountListener::class)]
class CountListenerTest extends KernelTestCase
{

    public function test_post_created_dispatches_one_bundled_message_for_posts_authors_and_tags(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $post = PostFactory::createOneFor($blog);

        $this->getEd()->dispatch(new PostCreatedEvent($post));

        $this->assertSinglePostGroupMessage($blog);
    }

    public function test_post_deleted_dispatches_one_bundled_message_for_posts_authors_and_tags(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $post = PostFactory::createOneFor($blog);

        $this->getEd()->dispatch(new PostDeletedEvent($post));

        $this->assertSinglePostGroupMessage($blog);
    }

    public function test_post_updated_dispatches_one_bundled_message_for_posts_authors_and_tags(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $post = PostFactory::createOneFor($blog);

        $this->getEd()->dispatch(new PostUpdatedEvent($post));

        $this->assertSinglePostGroupMessage($blog);
    }

    public function test_post_variant_published_dispatches_one_bundled_message_for_posts_authors_and_tags(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $post = PostFactory::createOneFor($blog);
        $variant = PostVariantFactory::createOne(['post' => $post]);

        $this->getEd()->dispatch(new PostVariantPublishedEvent($variant));

        $this->assertSinglePostGroupMessage($blog);
    }

    public function test_post_variant_unpublished_dispatches_one_bundled_message_for_posts_authors_and_tags(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $post = PostFactory::createOneFor($blog);
        $variant = PostVariantFactory::createOne(['post' => $post]);

        $this->getEd()->dispatch(new PostVariantUnpublishedEvent($variant));

        $this->assertSinglePostGroupMessage($blog);
    }

    public function test_user_created_dispatches_user_recalculation(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);

        $this->getEd()->dispatch(new UserCreatedEvent($user));

        $this->assertOnlyMessage($blog, [CountType::USERS]);
    }

    public function test_user_deleted_dispatches_user_recalculation(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);

        $this->getEd()->dispatch(new UserDeletedEvent($user));

        $this->assertOnlyMessage($blog, [CountType::USERS]);
    }

    public function test_media_created_dispatches_media_recalculation(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $media = MediaFactory::createOne(['blog' => $blog]);

        $this->getEd()->dispatch(new MediaCreatedEvent($media));

        $this->assertOnlyMessage($blog, [CountType::MEDIA]);
    }

    public function test_media_deleted_dispatches_media_recalculation(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $media = MediaFactory::createOne(['blog' => $blog]);

        $this->getEd()->dispatch(new MediaDeletedEvent($media));

        $this->assertOnlyMessage($blog, [CountType::MEDIA]);
    }

    private function assertSinglePostGroupMessage(Blog $blog): void
    {
        $this->assertOnlyMessage($blog, [CountType::POSTS, CountType::AUTHORS, CountType::TAGS]);
    }

    /**
     * @param CountType[] $expectedTypes
     */
    private function assertOnlyMessage(Blog $blog, array $expectedTypes): void
    {
        /** @var RecalculateCountMessage[] $messages */
        $messages = array_values(array_filter(
            $this->transport(MessageTransport::ASYNC)->queue()->messages(RecalculateCountMessage::class),
            static fn(RecalculateCountMessage $message) => $message->blogId === $blog->getId(),
        ));

        $this->assertCount(1, $messages);

        $sort = static fn(CountType $a, CountType $b) => $a->value <=> $b->value;
        $actualTypes = $messages[0]->types;
        usort($actualTypes, $sort);
        usort($expectedTypes, $sort);

        $this->assertSame($expectedTypes, $actualTypes);
    }

}
