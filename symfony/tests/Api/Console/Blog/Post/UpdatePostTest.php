<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
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
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
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
}
