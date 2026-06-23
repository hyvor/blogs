<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Api\Console\Object\PostObject;
use App\Api\Console\Object\PostObjectFactory;
use App\Entity\Enum\PostVariantStatus;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
#[CoversClass(PostObject::class)]
#[CoversClass(PostObjectFactory::class)]
class GetPostsTest extends ApiTestCase
{
    public function test_fetches_posts(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
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
        $this->assertArrayHasKey('id', $json[0]);
        $this->assertArrayHasKey('variants', $json[0]);
    }

    public function test_fetches_posts_with_limit_and_offset(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-offset']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
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
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
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

    public function test_does_not_return_pages(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'posts-get-no-pages']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $page = PostFactory::createOne(['blog' => $blog, 'is_page' => true]);
        PostVariantFactory::createOne(['post' => $page, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog, '/posts', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertFalse($json[0]['is_page']);
    }
}
