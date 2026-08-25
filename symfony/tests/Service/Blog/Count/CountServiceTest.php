<?php

namespace App\Tests\Service\Blog\Count;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Entity\Post;
use App\Service\Blog\Count\CountService;
use App\Service\Blog\Count\CountType;
use App\Service\Language\LanguageService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Lock\LockFactory;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(CountService::class)]
class CountServiceTest extends KernelTestCase
{

    private function countService(): CountService
    {
        return $this->getService(CountService::class);
    }

    private function primaryLanguage(Blog $blog): Language
    {
        return $this->getService(LanguageService::class)->getPrimaryLanguage($blog);
    }

    private function lockFactory(): LockFactory
    {
        return $this->getService(LockFactory::class);
    }

    private function lockKey(Blog $blog, CountType $type): string
    {
        return sprintf('count_recalculate_%d_%s', $blog->getId(), $type->value);
    }

    /**
     * @return array<string, int|float>
     */
    private function counts(Blog $blog): array
    {
        return $blog->getCounts() ?? [];
    }

    private function createPostWithVariant(
        Blog $blog,
        Language $language,
        PostVariantStatus $status,
        bool $isFeatured = false,
        bool $isPage = false,
    ): Post {
        $post = PostFactory::createOneFor($blog, ['is_featured' => $isFeatured, 'is_page' => $isPage]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => $status]);
        return $post;
    }

    public function test_recalculates_post_counts(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $language = $this->primaryLanguage($blog);

        $this->createPostWithVariant($blog, $language, PostVariantStatus::PUBLISHED, isFeatured: true);
        $this->createPostWithVariant($blog, $language, PostVariantStatus::PUBLISHED);
        $this->createPostWithVariant($blog, $language, PostVariantStatus::DRAFT);
        $this->createPostWithVariant($blog, $language, PostVariantStatus::SCHEDULED);
        // pages are excluded from every count
        $this->createPostWithVariant($blog, $language, PostVariantStatus::PUBLISHED, isFeatured: true, isPage: true);

        $this->countService()->recalculate($blog, CountType::POSTS_OF_BLOG);

        refresh($blog);
        $counts = $this->counts($blog);
        $this->assertSame(2, $counts['posts']);
        $this->assertSame(1, $counts['posts_draft']);
        $this->assertSame(1, $counts['posts_scheduled']);
        $this->assertSame(1, $counts['posts_featured']);
    }

    public function test_recalculate_post_counts_preserves_other_count_keys(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $blog->setCounts(['users' => 5]);
        $this->getEm()->flush();

        $this->countService()->recalculate($blog, CountType::POSTS_OF_BLOG);

        refresh($blog);
        $counts = $this->counts($blog);
        $this->assertSame(5, $counts['users']);
        $this->assertSame(0, $counts['posts']);
    }

    public function test_recalculates_author_counts(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $language = $this->primaryLanguage($blog);
        $author = UserFactory::createOne(['blog' => $blog, 'posts_count' => 999]);
        $otherUser = UserFactory::createOne(['blog' => $blog, 'posts_count' => 999]);

        $published = $this->createPostWithVariant($blog, $language, PostVariantStatus::PUBLISHED);
        $published->getAuthors()->add($author);

        $draft = $this->createPostWithVariant($blog, $language, PostVariantStatus::DRAFT);
        $draft->getAuthors()->add($author);

        $this->getEm()->flush();

        $this->countService()->recalculate($blog, CountType::POSTS_OF_USERS);

        refresh($author);
        refresh($otherUser);
        $this->assertSame(1, $author->getPostsCount());
        $this->assertSame(0, $otherUser->getPostsCount());
    }

    public function test_recalculates_tag_counts(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $language = $this->primaryLanguage($blog);
        $tag = TagFactory::createOne(['blog' => $blog, 'posts_count' => 999]);
        $otherTag = TagFactory::createOne(['blog' => $blog, 'posts_count' => 999]);

        $published = $this->createPostWithVariant($blog, $language, PostVariantStatus::PUBLISHED);
        $published->getTags()->add($tag);

        $this->getEm()->flush();

        $this->countService()->recalculate($blog, CountType::POSTS_OF_TAGS);

        refresh($tag);
        refresh($otherTag);
        $this->assertSame(1, $tag->getPostsCount());
        $this->assertSame(0, $otherTag->getPostsCount());
    }

    public function test_recalculates_user_counts(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);
        UserFactory::createOne(['blog' => $blog]);
        UserFactory::createOne();

        $this->countService()->recalculate($blog, CountType::USERS_OF_BLOG);

        refresh($blog);
        $this->assertSame(2, $this->counts($blog)['users']);
    }

    public function test_recalculates_media_counts(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        MediaFactory::createOne(['blog' => $blog, 'size' => 100]);
        MediaFactory::createOne(['blog' => $blog, 'size' => 250]);
        MediaFactory::createOne();

        $this->countService()->recalculate($blog, CountType::MEDIA_OF_BLOG);

        refresh($blog);
        $this->assertSame(350, $this->counts($blog)['media']);
    }

    public function test_recalculate_is_idempotent(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);

        $service = $this->countService();
        $service->recalculate($blog, CountType::USERS_OF_BLOG);
        $service->recalculate($blog, CountType::USERS_OF_BLOG);

        refresh($blog);
        $this->assertSame(1, $this->counts($blog)['users']);
    }

    public function test_skips_recalculation_when_the_same_blog_and_type_are_already_locked(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);

        $lock = $this->lockFactory()->createLock($this->lockKey($blog, CountType::USERS_OF_BLOG));
        $this->assertTrue($lock->acquire());

        $this->countService()->recalculate($blog, CountType::USERS_OF_BLOG);

        refresh($blog);
        $this->assertNull($blog->getCounts());
    }

    public function test_releases_the_lock_after_recalculating(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);

        $this->countService()->recalculate($blog, CountType::USERS_OF_BLOG);

        $lock = $this->lockFactory()->createLock($this->lockKey($blog, CountType::USERS_OF_BLOG));
        $this->assertTrue($lock->acquire());
    }

    public function test_the_lock_does_not_block_a_different_type_for_the_same_blog(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);
        MediaFactory::createOne(['blog' => $blog, 'size' => 10]);

        $lock = $this->lockFactory()->createLock($this->lockKey($blog, CountType::USERS_OF_BLOG));
        $this->assertTrue($lock->acquire());

        $this->countService()->recalculate($blog, CountType::MEDIA_OF_BLOG);

        refresh($blog);
        $this->assertSame(10, $this->counts($blog)['media']);
    }

    public function test_lock_does_not_block_when_entity_ids_are_provided(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $language = $this->primaryLanguage($blog);
        $author = UserFactory::createOne(['blog' => $blog, 'posts_count' => 999]);

        $published = $this->createPostWithVariant($blog, $language, PostVariantStatus::PUBLISHED);
        $published->getAuthors()->add($author);
        $this->getEm()->flush();

        $lock = $this->lockFactory()->createLock($this->lockKey($blog, CountType::POSTS_OF_USERS));
        $this->assertTrue($lock->acquire());

        $this->countService()->recalculate($blog, CountType::POSTS_OF_USERS, [$author->getId()]);

        refresh($author);
        $this->assertSame(1, $author->getPostsCount());
    }

}
