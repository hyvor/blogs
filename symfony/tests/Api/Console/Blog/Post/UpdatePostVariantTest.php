<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserStatus;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
class UpdatePostVariantTest extends ApiTestCase
{
    public function test_updates_variant_fields(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-update']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT, 'slug' => null]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'title' => 'Hello World',
            'description' => 'A description',
            'slug' => 'hello-world',
            'status' => 'draft',
            'seo_primary_keyword' => 'hello',
            'seo_secondary_keywords' => ['world', 'test'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertSame($language->getId(), $json['language_id']);
        $this->assertSame('Hello World', $json['title']);
        $this->assertSame('A description', $json['description']);
        $this->assertSame('hello-world', $json['slug']);
        $this->assertSame('hello', $json['seo_primary_keyword']);
        $this->assertSame(['world', 'test'], $json['seo_secondary_keywords']);
    }

    public function test_auto_generates_slug_when_publishing_without_slug(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-pub']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => null,
            'title' => 'My Post Title',
            'seo_primary_keyword' => 'my-post-title',
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'status' => 'published',
            'seo_primary_keyword' => null,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertSame('published', $json['status']);
        $this->assertNotEmpty($json['slug']);
        $this->assertNull($json['seo_primary_keyword']);
    }

    public function test_fails_if_slug_already_taken(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-slug-taken']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post1 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post1, 'language' => $language, 'slug' => 'existing-slug']);

        $post2 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post2, 'language' => $language, 'slug' => null]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post2->getId() . '/variant', [
            'language_id' => $language->getId(),
            'slug' => 'existing-slug',
        ], user: $user);

        $this->assertResponseFailed(422, 'Slug has already been taken');
    }

    public function test_fails_if_slug_has_invalid_characters(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-slug-invalid']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'slug' => 'invalid/slug',
        ], user: $user);

        $this->assertResponseFailed(422, 'Slug cannot contain /');
    }

    public function test_creates_redirect_on_slug_change(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-redirect', 'hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        RouteFactory::createDefaultsFor($blog);

        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'slug' => 'old-slug', 'status' => PostVariantStatus::PUBLISHED]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'slug' => 'new-slug',
            'redirect_on_slug_change' => true,
        ], user: $user);

        $this->assertResponseIsSuccessful();

        $redirect = $this->getEm()->getConnection()->fetchOne(
            "SELECT COUNT(*) FROM redirects WHERE blog_id = ? AND path = '/old-slug'",
            [$blog->getId()],
        );
        $this->assertSame(1, (int) $redirect);
    }
}
