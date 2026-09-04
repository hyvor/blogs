<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserRole;
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
    private const string CONTENT_UNSAVED = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hello World"}]}]}';

    public function test_variant_not_found(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => 999999,
        ], user: $user);

        $this->assertResponseFailed(404, 'Variant not found');
    }

    #[TestWith([PostVariantStatus::PUBLISHED])]
    #[TestWith([PostVariantStatus::SCHEDULED])]
    public function test_fails_when_already_published_or_scheduled(
        PostVariantStatus $status
    ): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => $status,
            'slug' => 'my-post',
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
        ], user: $user);

        $this->assertResponseFailed(422, 'Post variant is already ' . $status->value);
    }

    public function test_fails_when_content_unsaved_is_null(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'content_unsaved' => null,
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
        ], user: $user);

        $this->assertResponseFailed(422, 'Cannot publish post variant with no content');
    }

    public function test_publishes_draft_variant(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'title' => 'My Post',
            'content_unsaved' => self::CONTENT_UNSAVED,
        ]);

        $response = $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string)$response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('published', $data['status']);
        $this->assertSame('my-post', $data['slug']);

        $this->getEm()->refresh($variant);
        $this->assertNotNull($variant->getPublishedAt());

        $this->assertSame(self::CONTENT_UNSAVED, $variant->getContent());
        $this->assertSame('<p>Hello World</p>', $variant->getContentHtml());
        $this->assertStringContainsString('Hello World', (string)$variant->getContentText());
        $this->assertSame(2, $variant->getWords());
    }

    public function test_schedules_variant(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'title' => 'My Post',
            'content_unsaved' => self::CONTENT_UNSAVED,
        ]);

        $publishAt = (new \DateTimeImmutable('+1 day'))->getTimestamp();

        $response = $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
            'publish_at' => $publishAt,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string)$response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('scheduled', $data['status']);

        $this->getEm()->refresh($variant);
        $publishedAt = $variant->getPublishedAt();
        $this->assertNotNull($publishedAt);
        $this->assertSame($publishAt, $publishedAt->getTimestamp());
        $this->assertSame(PostVariantStatus::SCHEDULED, $variant->getStatus());
        $this->assertSame(self::CONTENT_UNSAVED, $variant->getContent());
    }

    public function test_generates_slug_if_missing(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => null,
            'title' => 'My Post Title',
            'content_unsaved' => self::CONTENT_UNSAVED,
        ]);

        $response = $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string)$response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('published', $data['status']);
        $this->assertNotNull($data['slug']);
        $this->assertSame('my-post-title', $data['slug']);
    }

    public function test_blocks_publish_when_content_has_pending_suggestions(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'publish-blocked-suggestions']);
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);

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

        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'content_unsaved' => $contentWithPendingSuggestion,
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
        ], user: $user);

        $this->assertResponseFailed(422, 'unresolved suggestions');

        $this->getEm()->refresh($variant);
        $this->assertNull($variant->getPublishedAt());
    }

    public function test_author_with_publish_own_can_publish(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'author-pub-own']);
        RouteFactory::createDefaultsFor($blog);
        $author = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $post->getAuthors()->add($author);
        $this->getEm()->flush();

        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'content_unsaved' => self::CONTENT_UNSAVED,
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
        ], user: $author);

        $this->assertResponseIsSuccessful();
        $this->getEm()->refresh($variant);
        $this->assertSame(PostVariantStatus::PUBLISHED, $variant->getStatus());
    }

    public function test_non_author_with_publish_all_can_publish(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'editor-pub-all']);
        RouteFactory::createDefaultsFor($blog);
        $author = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);
        $editor = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $post->getAuthors()->add($author);
        $this->getEm()->flush();

        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'content_unsaved' => self::CONTENT_UNSAVED,
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
        ], user: $editor);

        $this->assertResponseIsSuccessful();
        $this->getEm()->refresh($variant);
        $this->assertSame(PostVariantStatus::PUBLISHED, $variant->getStatus());
    }

    public function test_non_author_without_publish_all_cannot_publish(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'writer-pub-blocked']);
        RouteFactory::createDefaultsFor($blog);
        $author = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);
        $otherWriter = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $post->getAuthors()->add($author);
        $this->getEm()->flush();

        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'my-post',
            'content_unsaved' => self::CONTENT_UNSAVED,
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/publish', [
            'post_variant_id' => $variant->getId(),
        ], user: $otherWriter);

        $this->assertResponseFailed(403, 'You do not have permission to publish this post because you are not an author.');
        $this->getEm()->refresh($variant);
        $this->assertNull($variant->getPublishedAt());
    }

    public function test_non_author_without_publish_all_cannot_unpublish(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'writer-unpub-blocked']);
        RouteFactory::createDefaultsFor($blog);
        $author = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);
        $otherWriter = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $post->getAuthors()->add($author);
        $this->getEm()->flush();

        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'my-post',
            'content_unsaved' => self::CONTENT_UNSAVED,
        ]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/unpublish', [
            'post_variant_id' => $variant->getId(),
        ], user: $otherWriter);

        $this->assertResponseFailed(403, 'You do not have permission to publish this post because you are not an author.');
        $this->getEm()->refresh($variant);
        $this->assertSame(PostVariantStatus::PUBLISHED, $variant->getStatus());
    }
}
