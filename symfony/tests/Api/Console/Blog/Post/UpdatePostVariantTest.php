<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\RedirectType;
use App\Entity\Enum\UserStatus;
use App\Entity\Redirect;
use App\Service\Post\Content\Validation\ProsemirrorJson;
use App\Service\Post\Content\Validation\ProsemirrorJsonValidator;
use App\Service\Post\Event\PostVariantUpdatedEvent;
use App\Service\Post\PostService;
use App\Service\Post\Suggestion\PostSuggestionContentChecker;
use App\Service\Redirect\Event\RedirectChangedEvent;
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
#[CoversClass(ProsemirrorJson::class)]
#[CoversClass(ProsemirrorJsonValidator::class)]
#[CoversClass(PostSuggestionContentChecker::class)]
class UpdatePostVariantTest extends ApiTestCase
{

    // content cannot be updated via this endpoint anymore, use document operations
//    #[TestWith(['{"invalid_json": true,}', 'Unable to decode JSON'])]
//    #[TestWith(['{"type": "notdoc"}', 'The top node must be a doc node'])]
//    public function test_fails_when_json_invalid(string|int $content, string $error): void
//    {
//        $blog = BlogFactory::createOneWithPrimaryLanguage();
//        $user = UserFactory::createOne(['blog' => $blog]);
//        $post = PostFactory::createOne(['blog' => $blog]);
//        $language = $blog->getLanguages()->first();
//        $this->assertNotFalse($language);
//        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);
//
//        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
//            'language_id' => $language->getId(),
//            'content' => $content, // Invalid JSON
//        ], user: $user);
//
//        $this->assertResponseFailed(422, 'The value must be a valid Prosemirror JSON. Error: ' . $error);
//    }

    public function test_when_language_not_found(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $blog->getLanguages()->first()]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => 9999, // Non-existent language ID
        ], user: $user);

        $this->assertResponseFailed(422, 'Language not found');
    }

    public function test_when_variant_not_found(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        // No variant created for this post
        $language = $blog->getLanguages()->first();
        $this->assertNotFalse($language);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseFailed(404, 'Variant not found');
    }

    public function test_updates_variant_fields(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-update']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT, 'slug' => null]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'title' => 'Hello World',
            'description' => 'A description',
            'slug' => 'hello-world',
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

        $this->getEd()->assertDispatched(PostVariantUpdatedEvent::class);
    }

    public function test_clears_seo_primary_keyword(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-seo']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'seo_primary_keyword' => 'my-post-title',
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'seo_primary_keyword' => null,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

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

        $redirect = $this->getEm()->getRepository(Redirect::class)->findOneBy([
            'blog' => $blog,
            'path' => '/old-slug',
        ]);
        $this->assertNotNull($redirect);
        $this->assertSame('/new-slug', $redirect->getTo());
        $this->assertSame(RedirectType::PERMANENT, $redirect->getType());

        $this->getEd()->assertDispatched(PostVariantUpdatedEvent::class);
        $this->getEd()->assertDispatched(RedirectChangedEvent::class);
    }

    public function test_fails_when_content_updated_at_is_set_when_published_at_is_null(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-content-updated-at-unpublished']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog, 'published_at' => null]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'content_updated_at' => (new \DateTimeImmutable())->getTimestamp(),
        ], user: $user);

        $this->assertResponseFailed(422, 'Cannot set content_updated_at for unpublished post');
    }

    public function test_fails_when_content_updated_at_is_smaller_than_published_at(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-content-updated-at-too-early']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $publishedAt = new \DateTimeImmutable();
        $post = PostFactory::createOne(['blog' => $blog, 'published_at' => $publishedAt]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::PUBLISHED]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'content_updated_at' => $publishedAt->modify('-1 hour')->getTimestamp(),
        ], user: $user);

        $this->assertResponseFailed(422, 'Content updated time should be after published time');
    }

//    private function contentWithPendingSuggestion(): string
//    {
//        $json = json_encode([
//            'type' => 'doc',
//            'content' => [[
//                'type' => 'paragraph',
//                'attrs' => ['suggestions' => null],
//                'content' => [[
//                    'type' => 'text',
//                    'text' => 'hello',
//                    'marks' => [['type' => 'suggestion', 'attrs' => ['type' => 'insert', 'id' => 'sg-1']]],
//                ]],
//            ]],
//        ]);
//        $this->assertNotFalse($json);
//        return $json;
//    }

//    public function test_allows_saving_a_draft_with_pending_suggestions(): void
//    {
//        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-draft-suggestions']);
//        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
//        $language = LanguageFactory::createOnePrimaryFor($blog);
//        $post = PostFactory::createOne(['blog' => $blog]);
//        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);
//
//        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
//            'language_id' => $language->getId(),
//            'content' => $this->contentWithPendingSuggestion(),
//        ], user: $user);
//
//        // a draft's `content` isn't public yet - autosaving with pending suggestions must not be blocked
//        $this->assertResponseIsSuccessful();
//    }

//    public function test_blocks_updating_a_published_variants_content_with_pending_suggestions(): void
//    {
//        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-published-suggestions']);
//        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
//        $language = LanguageFactory::createOnePrimaryFor($blog);
//        $post = PostFactory::createOne(['blog' => $blog]);
//        PostVariantFactory::createOne([
//            'post' => $post,
//            'language' => $language,
//            'status' => PostVariantStatus::PUBLISHED,
//            'slug' => 'already-published',
//        ]);
//
//        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
//            'language_id' => $language->getId(),
//            'content' => $this->contentWithPendingSuggestion(),
//        ], user: $user);
//
//        $this->assertResponseFailed(422, 'unresolved suggestions');
//    }
}
