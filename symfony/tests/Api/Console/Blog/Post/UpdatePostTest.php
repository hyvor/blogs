<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\UserStatus;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
class UpdatePostTest extends ApiTestCase
{
    public function test_updates_post_fields(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-update']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog, 'is_featured' => false]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $timestamp = mktime(12, 0, 0, 1, 1, 2024);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId(), [
            'is_featured' => true,
            'canonical_url' => 'https://example.com',
            'featured_image_url' => 'https://example.com/img.jpg',
            'code_head' => '<script>head</script>',
            'code_foot' => '<script>foot</script>',
            'published_at' => $timestamp,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertTrue($json['is_featured']);
        $this->assertSame('https://example.com', $json['canonical_url']);
        $this->assertSame('https://example.com/img.jpg', $json['featured_image_url']);
        $this->assertSame('<script>head</script>', $json['code_head']);
        $this->assertSame('<script>foot</script>', $json['code_foot']);
        $this->assertSame($timestamp, $json['published_at']);

        $post = refresh($post);
        $this->assertTrue($post->isFeatured());
        $this->assertSame('https://example.com', $post->getCanonicalUrl());
        $this->assertSame('https://example.com/img.jpg', $post->getFeaturedImageUrl());
        $this->assertSame('<script>head</script>', $post->getCodeHead());
        $this->assertSame('<script>foot</script>', $post->getCodeFoot());
        $this->assertSame($timestamp, $post->getPublishedAt()?->getTimestamp());
    }

    public function test_nullifies_post_fields(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-update-null']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne([
            'blog' => $blog,
            'canonical_url' => 'https://example.com',
            'featured_image_url' => 'https://example.com/img.jpg',
            'code_head' => '<script>head</script>',
            'code_foot' => '<script>foot</script>',
        ]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId(), [
            'canonical_url' => null,
            'featured_image_url' => null,
            'code_head' => null,
            'code_foot' => null,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertNull($json['canonical_url']);
        $this->assertNull($json['featured_image_url']);
        $this->assertNull($json['code_head']);
        $this->assertNull($json['code_foot']);

        $post = refresh($post);
        $this->assertNull($post->getCanonicalUrl());
        $this->assertNull($post->getFeaturedImageUrl());
        $this->assertNull($post->getCodeHead());
        $this->assertNull($post->getCodeFoot());
    }
}
