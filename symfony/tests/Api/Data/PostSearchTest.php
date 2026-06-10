<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\PostsController;
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
class PostSearchTest extends ApiTestCase
{
    private function createBlogWithLanguageAndRoutes(): array
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $lang = LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'is_enabled' => true]);
        return [$blog, $lang];
    }

    private function createPost(mixed $blog, mixed $lang, array $variantAttrs, array $postAttrs = []): mixed
    {
        $post = PostFactory::createOne(array_merge([
            'blog' => $blog,
            'is_page' => false,
            'published_at' => new \DateTimeImmutable(),
        ], $postAttrs));

        PostVariantFactory::createOne(array_merge([
            'post' => $post,
            'language' => $lang,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'search-' . $post->getId(),
        ], $variantAttrs));

        return $post;
    }

    public function test_searches_posts_in_english(): void
    {
        [$blog, $lang] = $this->createBlogWithLanguageAndRoutes();

        $cake = $this->createPost($blog, $lang, [
            'title' => 'How to make a cake',
            'ts_language' => 'english',
        ]);

        $pie = $this->createPost($blog, $lang, [
            'title' => 'How to make a pie',
            'ts_language' => 'english',
        ]);

        // Not published - should not appear
        $this->createPost($blog, $lang, [
            'title' => 'How to make a cake',
            'ts_language' => 'english',
            'status' => PostVariantStatus::DRAFT,
        ]);

        $this->dataApi($blog, '/posts/search', ['search' => 'cake']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame('How to make a cake', $json['data'][0]['title']);
    }

    public function test_searches_with_partial_words(): void
    {
        [$blog, $lang] = $this->createBlogWithLanguageAndRoutes();

        $this->createPost($blog, $lang, [
            'title' => 'Wordpress Alternatives',
            'ts_language' => 'english',
        ]);

        $this->createPost($blog, $lang, [
            'title' => 'Ghost Alternatives',
            'ts_language' => 'english',
        ]);

        $this->dataApi($blog, '/posts/search', ['search' => 'word']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame('Wordpress Alternatives', $json['data'][0]['title']);
    }

    public function test_does_not_work_without_search_query(): void
    {
        [$blog, $lang] = $this->createBlogWithLanguageAndRoutes();

        $this->dataApi($blog, '/posts/search');

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_returns_correct_total(): void
    {
        [$blog, $lang] = $this->createBlogWithLanguageAndRoutes();

        $this->createPost($blog, $lang, ['title' => 'hyvor is good', 'ts_language' => 'english']);
        $this->createPost($blog, $lang, ['title' => 'hyvor blog', 'ts_language' => 'english']);
        $this->createPost($blog, $lang, ['title' => 'something else', 'ts_language' => 'english']);

        $this->dataApi($blog, '/posts/search', ['search' => 'hyvor', 'limit' => 1]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame(2, $json['pagination']['total']);
    }

    public function test_returns_paginated_results(): void
    {
        [$blog, $lang] = $this->createBlogWithLanguageAndRoutes();

        for ($i = 0; $i < 3; $i++) {
            $this->createPost($blog, $lang, [
                'title' => 'hyvor result ' . $i,
                'ts_language' => 'english',
            ]);
        }

        $this->dataApi($blog, '/posts/search', ['search' => 'hyvor', 'limit' => 2, 'page' => 1]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json['data']);
        $this->assertSame(3, $json['pagination']['total']);
    }
}
