<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\PostVariantStatus;
use App\Service\Post\PostService;
use App\Service\Post\PostSlugService;
use App\Service\Post\Suggestion\PostSuggestionContentChecker;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
#[CoversClass(PostSlugService::class)]
#[CoversClass(PostSuggestionContentChecker::class)]
class PublishPostVariantTest extends ApiTestCase
{
    public function test_language_not_found(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'language_id' => 9999,
        ], user: $user);

        $this->assertResponseFailed(422, 'Language not found');
    }

    public function test_variant_not_found(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseFailed(404, 'Variant not found');
    }

    #[TestWith(PostVariantStatus::PUBLISHED)]
    #[TestWith(PostVariantStatus::SCHEDULED)]
    public function test_fails_when_already_pubslihed_or_scheduled(
        PostVariantStatus $status
    ): void
    {
        //
    }

    public function test_publishes_draft_variant(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);
        $post = PostFactory::createOne(['blog' => $blog, 'published_at' => null]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'title' => 'My Post',
        ]);

        $response = $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string)$response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('published', $data['status']);
        $this->assertSame('my-post', $data['slug']);

        $this->getEm()->refresh($post);
        $this->assertNotNull($post->getPublishedAt());
    }

    public function test_generates_slug_if_missing(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);
        $post = PostFactory::createOne(['blog' => $blog, 'published_at' => null]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => null,
            'title' => 'My Post Title',
        ]);

        $response = $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string)$response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('published', $data['status']);
        $this->assertNotNull($data['slug']);
        $this->assertNotEmpty($data['slug']);
    }

    public function test_does_not_overwrite_existing_published_at(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);
        $existingDate = new \DateTimeImmutable('2020-01-01');
        $post = PostFactory::createOne(['blog' => $blog, 'published_at' => $existingDate]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'some-slug',
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->getEm()->refresh($post);
        $publishedAt = $post->getPublishedAt();
        $this->assertNotNull($publishedAt);
        $this->assertSame($existingDate->getTimestamp(), $publishedAt->getTimestamp());
    }

    public function test_blocks_publish_when_content_has_pending_suggestions(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'publish-blocked-suggestions']);
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog, 'published_at' => null]);

        $contentWithPendingSuggestion = json_encode([
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'attrs' => ['suggestions' => null],
                'content' => [[
                    'type' => 'text',
                    'text' => 'hello',
                    'marks' => [['type' => 'suggestion', 'attrs' => ['type' => 'insert', 'id' => 'sg-1']]],
                ]],
            ]],
        ]);

        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'content' => $contentWithPendingSuggestion,
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseFailed(422, 'unresolved suggestions');

        $this->getEm()->refresh($post);
        $this->assertNull($post->getPublishedAt());
    }
}
