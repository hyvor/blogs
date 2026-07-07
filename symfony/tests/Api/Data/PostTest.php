<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\PostsController;
use App\Api\Data\Factory\PostObjectFactory;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostsController::class)]
#[CoversClass(PostObjectFactory::class)]
class PostTest extends ApiTestCase
{
    /**
     * @return array{0: \App\Entity\Blog, 1: \App\Entity\Language, 2: \App\Entity\Language, 3: \App\Entity\Post, 4: \App\Entity\PostVariant, 5: \App\Entity\PostVariant}
     */
    private function createBlogWithPost(): array
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $lang1 = LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        $lang2 = LanguageFactory::createOne(['blog' => $blog, 'code' => 'fr', 'is_primary' => false]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'is_enabled' => true]);

        $post = PostFactory::createOne([
            'blog' => $blog,
            'is_page' => false,
            'is_featured' => false,
            'published_at' => new \DateTimeImmutable(),
        ]);

        $variant1 = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang1,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'test-post-en',
            'title' => 'Test Post',
        ]);

        $variant2 = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang2,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'test-post-fr',
            'title' => 'Test Post FR',
        ]);

        // other blog post
        $otherBlogPost = PostFactory::createOne();
        PostVariantFactory::createOne([
            'post' => $otherBlogPost,
            'language' => $lang1,
            'status' => PostVariantStatus::PUBLISHED,
        ]);

        return [$blog, $lang1, $lang2, $post, $variant1, $variant2];
    }

    public function test_works_with_post_id(): void
    {
        [$blog, $lang1, $lang2, $post] = $this->createBlogWithPost();

        $this->dataApi($blog, '/post', ['id' => $post->getId()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($post->getId(), $json['id']);
        $this->assertIsArray($json['language']);
        $this->assertSame('en', $json['language']['code']);
    }

    public function test_requires_integer_id(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);

        $this->dataApi($blog, '/post', ['id' => 'something']);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_works_with_post_slug(): void
    {
        [$blog, $lang1, $lang2, $post, $variant1] = $this->createBlogWithPost();

        $this->dataApi($blog, '/post', ['slug' => $variant1->getSlug()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($post->getId(), $json['id']);
    }

    public function test_works_with_id_and_lang(): void
    {
        [$blog, $lang1, $lang2, $post] = $this->createBlogWithPost();

        $this->dataApi($blog, '/post', ['id' => $post->getId(), 'language' => 'fr']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($post->getId(), $json['id']);
        $this->assertIsArray($json['language']);
        $this->assertSame('fr', $json['language']['code']);
        $this->assertSame('test-post-fr', $json['slug']);
    }

    public function test_does_not_work_with_invalid_language(): void
    {
        [$blog, $lang1, $lang2, $post] = $this->createBlogWithPost();

        $this->dataApi($blog, '/post', ['id' => $post->getId(), 'language' => 'jp']);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_returns_404_for_missing_posts(): void
    {
        [$blog, $lang1] = $this->createBlogWithPost();

        $this->dataApi($blog, '/post', ['id' => 999999]);

        $this->assertResponseFailed(404, 'Post not found');
    }

    public function test_returns_404_for_missing_variant(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $lang1 = LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        $lang2 = LanguageFactory::createOne(['blog' => $blog, 'code' => 'fr', 'is_primary' => false]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'is_enabled' => true]);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false, 'published_at' => new \DateTimeImmutable()]);
        // Only create EN variant, no FR
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang1,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'only-en-' . $post->getId(),
        ]);

        $this->dataApi($blog, '/post', ['id' => $post->getId(), 'language' => 'fr']);

        $this->assertResponseFailed(404, 'Post not found: variant for language not found');
    }

    public function test_does_not_return_unpublished_posts(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $lang = LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false, 'published_at' => new \DateTimeImmutable()]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'draft-post-' . $post->getId(),
        ]);

        $this->dataApi($blog, '/post', ['id' => $post->getId()]);

        $this->assertResponseFailed(404, 'Post not found: not published');
    }

    public function test_does_not_return_posts_when_blog_is_wrong(): void
    {
        [$blog, $lang1, $lang2, $post] = $this->createBlogWithPost();

        $this->dataApi('nonexistent-999', '/post', ['id' => $post->getId()]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_filters_keys(): void
    {
        [$blog, $lang1, $lang2, $post] = $this->createBlogWithPost();

        $this->dataApi($blog, '/post', ['id' => $post->getId(), 'keys' => 'id,slug']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('slug', $json);
        $this->assertArrayNotHasKey('url', $json);
    }
}
