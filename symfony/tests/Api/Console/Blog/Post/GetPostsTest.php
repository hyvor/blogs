<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Api\Console\Object\PostList\PostListObject;
use App\Api\Console\Object\PostList\PostListObjectFactory;
use App\Api\Console\Object\PostVariantSummaryObject;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserStatus;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
#[CoversClass(PostListObject::class)]
#[CoversClass(PostListObjectFactory::class)]
#[CoversClass(PostVariantSummaryObject::class)]
class GetPostsTest extends ApiTestCase
{
    public function test_fetches_posts(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        for ($i = 0; $i < 3; $i++) {
            $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
            PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);
        }

        PostFactory::createOne(['blog' => $blog, 'is_page' => true]);

        $this->consoleBlogApi('GET', $blog, '/posts', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json);
        $this->assertIsArray($json[0]);
        $this->assertArrayHasKey('id', $json[0]);
        $this->assertArrayHasKey('variant_statuses', $json[0]);
    }

    public function test_fetches_posts_with_limit_and_offset(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-offset']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        for ($i = 0; $i < 5; $i++) {
            $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
            PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::PUBLISHED]);
        }

        $this->consoleBlogApi('GET', $blog, '/posts?limit=2&offset=2', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json);
    }

    public function test_filters_posts_by_status(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-status']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post1 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post1, 'language' => $language, 'status' => PostVariantStatus::PUBLISHED]);

        $post2 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post2, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);

        $this->consoleBlogApi('GET', $blog, '/posts?status=published', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
    }

    public function test_filters_by_featured(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-featured']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post1 = PostFactory::createOne(['blog' => $blog, 'is_featured' => true]);
        PostVariantFactory::createOne(['post' => $post1, 'language' => $language]);

        $post2 = PostFactory::createOne(['blog' => $blog, 'is_featured' => false]);
        PostVariantFactory::createOne(['post' => $post2, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog, '/posts?status=featured', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertTrue($json[0]['is_featured']);
    }

    public function test_does_not_return_pages(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-no-pages']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $page = PostFactory::createOne(['blog' => $blog, 'is_page' => true]);
        PostVariantFactory::createOne(['post' => $page, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog, '/posts', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertFalse($json[0]['is_page']);
    }

    public function test_filters_by_tag_id(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-tag']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $tag = TagFactory::createOne(['blog' => $blog]);

        $post1 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post1, 'language' => $language]);
        $post1->getTags()->add($tag);

        $post2 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post2, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog, '/posts?tag_id=' . $tag->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertEquals($post1->getId(), $json[0]['id']);
    }

    public function test_filters_by_author_id(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-author']);
        $user1 = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $user2 = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post1 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post1, 'language' => $language]);
        $post1->getAuthors()->add($user1);

        $post2 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post2, 'language' => $language]);
        $post2->getAuthors()->add($user2);

        $this->consoleBlogApi('GET', $blog, '/posts?author_id=' . $user1->getId(), user: $user1);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertEquals($post1->getId(), $json[0]['id']);
    }

    public function test_filters_by_timestamps(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-timestamps']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post1 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post1, 'language' => $language, 'published_at' => new \DateTimeImmutable('-2 days')]);

        $post2 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post2, 'language' => $language, 'published_at' => new \DateTimeImmutable('-1 day')]);

        $startTimestamp = new \DateTimeImmutable('-36 hours')->getTimestamp();
        $endTimestamp = new \DateTimeImmutable('now')->getTimestamp();

        $this->consoleBlogApi('GET', $blog, '/posts?start_timestamp=' . $startTimestamp . '&end_timestamp=' . $endTimestamp, user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertEquals($post2->getId(), $json[0]['id']);
    }

    public function test_searches_posts(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        LanguageFactory::createOnePrimaryFor($blog);

        $post1 = PostFactory::createOneForWithVariants($blog, variantAttributes: ['title' => 'First Post']);
        $post2 = PostFactory::createOneForWithVariants($blog, variantAttributes: ['title' => 'Second Post']);

        $this->consoleBlogApi('GET', $blog, '/posts?search=First', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertEquals($post1->getId(), $json[0]['id']);
    }

    public function test_searches_with_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language1 = LanguageFactory::createOnePrimaryFor($blog);
        $language2 = LanguageFactory::createOne(['blog' => $blog]);

        $post1 = PostFactory::createOneForWithVariants($blog, variantAttributes: ['title' => 'First Post', 'language' => $language1]);
        $post2 = PostFactory::createOneForWithVariants($blog, variantAttributes: ['title' => 'Second Post', 'language' => $language2]);

        $this->consoleBlogApi('GET', $blog, '/posts?search=Post&language_id=' . $language1->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertEquals($post1->getId(), $json[0]['id']);
    }
}
