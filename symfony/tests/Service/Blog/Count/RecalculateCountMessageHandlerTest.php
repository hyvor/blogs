<?php

namespace App\Tests\Service\Blog\Count;

use App\Entity\Enum\PostVariantStatus;
use App\Service\Blog\Count\CountService;
use App\Service\Blog\Count\CountType;
use App\Service\Blog\Count\RecalculateCountMessage;
use App\Service\Blog\Count\RecalculateCountMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(RecalculateCountMessage::class)]
#[CoversClass(RecalculateCountMessageHandler::class)]
#[CoversClass(CountService::class)]
class RecalculateCountMessageHandlerTest extends KernelTestCase
{

    public function test_recalculates_via_the_async_transport(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);
        UserFactory::createOne(['blog' => $blog]);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new RecalculateCountMessage($blog->getId(), [CountType::USERS_OF_BLOG]));
        $transport->processOrFail();

        refresh($blog);
        $counts = $blog->getCounts() ?? [];
        $this->assertSame(2, $counts['users']);
    }

    public function test_recalculates_every_type_bundled_in_the_message(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);
        MediaFactory::createOne(['blog' => $blog, 'size' => 42]);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new RecalculateCountMessage($blog->getId(), [CountType::USERS_OF_BLOG, CountType::MEDIA_OF_BLOG]));
        $transport->processOrFail();

        refresh($blog);
        $counts = $blog->getCounts() ?? [];
        $this->assertSame(1, $counts['users']);
        $this->assertSame(42, $counts['media']);
    }

    public function test_recalculates_only_given_entity_ids_users(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user1 = UserFactory::createOne(['blog' => $blog, 'posts_count' => 0]);
        $user2 = UserFactory::createOne(['blog' => $blog, 'posts_count' => 0]);

        $post = PostFactory::createOneFor($blog);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $blog->getLanguages()[0],
            'status' => PostVariantStatus::PUBLISHED,
        ]);
        $post->getAuthors()->add($user1);
        $post->getAuthors()->add($user2);

        $this->getEm()->flush();

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new RecalculateCountMessage($blog->getId(), [CountType::POSTS_OF_USERS], [[$user1->getId()]]));
        $transport->processOrFail();

        refresh($user1);
        refresh($user2);
        $this->assertSame(1, $user1->getPostsCount());
        $this->assertSame(0, $user2->getPostsCount()); // not updated
    }

    public function test_recalculates_only_given_entity_ids_tags(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $tag1 = TagFactory::createOne(['blog' => $blog, 'posts_count' => 0]);
        $tag2 = TagFactory::createOne(['blog' => $blog, 'posts_count' => 0]);

        $post = PostFactory::createOneFor($blog);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $blog->getLanguages()[0],
            'status' => PostVariantStatus::PUBLISHED,
        ]);
        $post->getTags()->add($tag1);
        $post->getTags()->add($tag2);

        $this->getEm()->flush();

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new RecalculateCountMessage($blog->getId(), [CountType::POSTS_OF_TAGS], [[$tag1->getId()]]));
        $transport->processOrFail();

        refresh($tag1);
        refresh($tag2);
        $this->assertSame(1, $tag1->getPostsCount());
        $this->assertSame(0, $tag2->getPostsCount()); // not updated
    }

    public function test_throws_when_blog_not_found(): void
    {
        $handler = $this->getService(RecalculateCountMessageHandler::class);

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $handler(new RecalculateCountMessage(-1, [CountType::USERS_OF_BLOG]));
    }

}
