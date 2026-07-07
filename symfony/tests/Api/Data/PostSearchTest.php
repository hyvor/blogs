<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\PostsController;
use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Entity\Post;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostsController::class)]
class PostSearchTest extends ApiTestCase
{

    private function primaryLanguage(Blog $blog): Language
    {
        $language = $blog->getLanguages()[0];
        assert($language instanceof Language);
        return $language;
    }

    /**
     * @param array<string, mixed> $variantAttrs
     * @param array<string, mixed> $postAttrs
     */
    private function createPost(Blog $blog, Language $lang, array $variantAttrs, array $postAttrs = []): Post
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
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $lang = $this->primaryLanguage($blog);

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
        $this->assertIsArray($json['data']);
        $this->assertCount(1, $json['data']);
        $this->assertIsArray($json['data'][0]);
        $this->assertSame('How to make a cake', $json['data'][0]['title']);
    }

    public function test_stemming(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $lang = $this->primaryLanguage($blog);

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

        $this->dataApi($blog, '/posts/search', ['search' => 'make']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['data']);
        $this->assertCount(2, $json['data']);
        $this->assertIsArray($json['data'][0]);
        $this->assertIsArray($json['data'][1]);
        $this->assertSame('How to make a cake', $json['data'][0]['title']);
        $this->assertSame('How to make a pie', $json['data'][1]['title']);
    }

    public function test_searches_in_french_also_searches_description_and_content(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $lang = $this->primaryLanguage($blog);

        $gateau = $this->createPost($blog, $lang, [
            'description' => 'Comment faire un gâteau',
            'ts_language' => 'french',
        ]);

        $etape = $this->createPost($blog, $lang, [
            'content_text' => 'Étape 1: mélanger les ingrédients',
            'ts_language' => 'french',
        ]);

        $this->dataApi($blog, '/posts/search', ['search' => 'étape']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['data']);
        $this->assertCount(1, $json['data']);
        $this->assertIsArray($json['data'][0]);
        $this->assertSame($etape->getId(), $json['data'][0]['id']);
    }

    public function test_searches_with_partial_words(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $lang = $this->primaryLanguage($blog);

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
        $this->assertIsArray($json['data']);
        $this->assertCount(1, $json['data']);
        $this->assertIsArray($json['data'][0]);
        $this->assertSame('Wordpress Alternatives', $json['data'][0]['title']);
    }

    public function test_does_not_work_without_search_query(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $lang = $this->primaryLanguage($blog);

        $this->dataApi($blog, '/posts/search');

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_priority_title_slug_description_content(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $lang = $this->primaryLanguage($blog);

        $inContent = $this->createPost($blog, $lang, [
            'content_text' => 'hyvor is good',
            'ts_language' => 'english',
        ]);

        $inDescription = $this->createPost($blog, $lang, [
            'description' => 'hyvor is good',
            'ts_language' => 'english',
        ]);

        $inSlug = $this->createPost($blog, $lang, [
            'ts_language' => 'english',
            'slug' => 'hyvor-is-good',
        ]);

        $inTitle = $this->createPost($blog, $lang, [
            'title' => 'hyvor is good',
            'ts_language' => 'english',
        ]);

        $this->dataApi($blog, '/posts/search', ['search' => 'hyvor is good']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['data']);
        $this->assertCount(4, $json['data']);
        $this->assertIsArray($json['data'][0]);
        $this->assertIsArray($json['data'][1]);
        $this->assertIsArray($json['data'][2]);
        $this->assertIsArray($json['data'][3]);
        // Title should come first
        $this->assertSame($inTitle->getId(), $json['data'][0]['id']);
        // Then slug
        $this->assertSame($inSlug->getId(), $json['data'][1]['id']);
        // Then description
        $this->assertSame($inDescription->getId(), $json['data'][2]['id']);
        // Then content
        $this->assertSame($inContent->getId(), $json['data'][3]['id']);
    }

    public function test_returns_correct_total(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $lang = $this->primaryLanguage($blog);

        $this->createPost($blog, $lang, ['title' => 'hyvor is good', 'ts_language' => 'english']);
        $this->createPost($blog, $lang, ['title' => 'hyvor blog', 'ts_language' => 'english']);
        $this->createPost($blog, $lang, ['title' => 'something else', 'ts_language' => 'english']);

        $this->dataApi($blog, '/posts/search', ['search' => 'hyvor', 'limit' => 1]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['pagination']);
        $this->assertSame(2, $json['pagination']['total']);
    }

    public function test_returns_paginated_results(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $lang = $this->primaryLanguage($blog);

        for ($i = 0; $i < 3; $i++) {
            $this->createPost($blog, $lang, [
                'title' => 'hyvor result ' . $i,
                'ts_language' => 'english',
            ]);
        }

        $this->dataApi($blog, '/posts/search', ['search' => 'hyvor', 'limit' => 2, 'page' => 1]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['data']);
        $this->assertCount(2, $json['data']);
        $this->assertIsArray($json['pagination']);
        $this->assertSame(3, $json['pagination']['total']);
    }
}
