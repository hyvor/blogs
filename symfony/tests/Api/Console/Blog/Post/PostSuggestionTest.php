<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostSuggestionController;
use App\Entity\Enum\PostSuggestionStatus;
use App\Entity\Enum\PostSuggestionType;
use App\Entity\PostSuggestion;
use App\Service\Post\PostService;
use App\Service\Post\Suggestion\PostSuggestionService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostSuggestionController::class)]
#[CoversClass(PostSuggestionService::class)]
#[CoversClass(PostService::class)]
class PostSuggestionTest extends ApiTestCase
{
    /**
     * Only one consoleBlogApi() call is allowed per test (AuthFake/BillingFake can't be
     * re-initialized within one test - see other Post/* tests), so any "existing"
     * suggestion/reply a test needs is created directly through PostSuggestionService
     * here rather than through a prior API call.
     */
    private function createSuggestionDirectly(
        \App\Entity\Blog $blog,
        \App\Entity\PostVariant $variant,
        string $id,
        PostSuggestionType $type,
        int $authorUserId,
    ): PostSuggestion {
        /** @var PostSuggestionService $service */
        $service = $this->getContainer()->get(PostSuggestionService::class);
        return $service->create($variant, $id, $type, $authorUserId);
    }

    public function test_create(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-create']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/suggestions', [
            'language_id' => $language->getId(),
            'id' => 'sg-1',
            'type' => 'insert',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $created = $this->getJson();
        $this->assertSame('sg-1', $created['id']);
        $this->assertSame('user:' . $user->getHyvorUserId(), $created['author']);
        $this->assertSame([], $created['comments']);
    }

    public function test_create_is_idempotent(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-idempotent']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language]);
        $this->createSuggestionDirectly($blog, $variant, 'sg-1', PostSuggestionType::INSERT, (int) $user->getHyvorUserId());

        // a create for the same id, with a different type, must not overwrite it
        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/suggestions', [
            'language_id' => $language->getId(),
            'id' => 'sg-1',
            'type' => 'delete',
        ], user: $user);

        $this->assertResponseIsSuccessful();

        /** @var string|int $count */
        $count = $this->getEm()->getConnection()->fetchOne(
            "SELECT COUNT(*) FROM post_suggestions WHERE id = 'sg-1'"
        );
        $this->assertSame(1, (int) $count);

        $type = $this->getEm()->getConnection()->fetchOne(
            "SELECT type FROM post_suggestions WHERE id = 'sg-1'"
        );
        $this->assertSame('insert', $type);
    }

    public function test_get_returns_existing_suggestion(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-get']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language]);
        $this->createSuggestionDirectly($blog, $variant, 'sg-1', PostSuggestionType::INSERT, (int) $user->getHyvorUserId());

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/suggestions/get', [
            'language_id' => $language->getId(),
            'ids' => ['sg-1', 'sg-does-not-exist'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $result = $this->getJson();
        $this->assertArrayHasKey('sg-1', $result);
        $this->assertArrayNotHasKey('sg-does-not-exist', $result);
        $entry = $result['sg-1'];
        $this->assertIsArray($entry);
        $this->assertSame('user:' . $user->getHyvorUserId(), $entry['author']);
    }

    public function test_get_does_not_return_suggestions_from_another_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-scoping']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $postA = PostFactory::createOne(['blog' => $blog]);
        $variantA = PostVariantFactory::createOne(['post' => $postA, 'language' => $language]);
        $this->createSuggestionDirectly($blog, $variantA, 'sg-a', PostSuggestionType::INSERT, (int) $user->getHyvorUserId());

        $postB = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $postB, 'language' => $language]);

        // asking for postA's suggestion id through postB's variant must not find it
        $this->consoleBlogApi('POST', $blog, '/post/' . $postB->getId() . '/variant/suggestions/get', [
            'language_id' => $language->getId(),
            'ids' => ['sg-a'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $result = $this->getJson();
        $this->assertSame([], $result);
    }

    public function test_reply(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-reply']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language]);
        $this->createSuggestionDirectly($blog, $variant, 'sg-1', PostSuggestionType::COMMENT, (int) $user->getHyvorUserId());

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/suggestions/sg-1/replies', [
            'language_id' => $language->getId(),
            'id' => 'reply-1',
            'content' => 'hello there',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $reply = $this->getJson();
        $this->assertSame('reply-1', $reply['id']);
        $this->assertSame('hello there', $reply['content']);
        $this->assertSame('user:' . $user->getHyvorUserId(), $reply['author']);
    }

    public function test_reply_auto_creates_missing_suggestion_using_fallback_type(): void
    {
        // simulates create/reply arriving out of order - the reply lands first
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-reply-race']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/suggestions/sg-race/replies', [
            'language_id' => $language->getId(),
            'id' => 'reply-1',
            'content' => 'first reply',
            'type' => 'format',
        ], user: $user);

        $this->assertResponseIsSuccessful();

        /** @var PostSuggestion|null $suggestion */
        $suggestion = $this->getEm()->getRepository(PostSuggestion::class)->find('sg-race');
        $this->assertNotNull($suggestion);
        $this->assertSame('format', $suggestion->getType()->value);
        $this->assertSame((int) $user->getHyvorUserId(), $suggestion->getAuthorUserId());
    }

    public function test_resolve_updates_status(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-resolve']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language]);
        $this->createSuggestionDirectly($blog, $variant, 'sg-1', PostSuggestionType::INSERT, (int) $user->getHyvorUserId());

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/suggestions/sg-1/resolve', [
            'language_id' => $language->getId(),
            'decision' => 'accept',
        ], user: $user);

        $this->assertResponseIsSuccessful();

        /** @var PostSuggestion $suggestion */
        $suggestion = $this->getEm()->getRepository(PostSuggestion::class)->find('sg-1');
        $this->assertSame(PostSuggestionStatus::ACCEPTED, $suggestion->getStatus());
    }

    public function test_resolve_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-resolve-404']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/suggestions/sg-nope/resolve', [
            'language_id' => $language->getId(),
            'decision' => 'accept',
        ], user: $user);

        $this->assertResponseFailed(404, 'Suggestion not found');
    }

    public function test_resolve_author_returns_deleted_user_for_unknown_id(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-author-deleted']);
        LanguageFactory::createOnePrimaryFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('GET', $blog, '/users/resolve-author?hyvor_user_id=999999', [], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('Deleted user', $json['name']);
        $this->assertNull($json['picture_url']);
    }

    public function test_resolve_author_returns_name_and_picture_for_known_user(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'suggestion-author-known']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $requester = UserFactory::createOne(['blog' => $blog]);
        $author = UserFactory::createOne([
            'blog' => $blog,
            'hyvor_user_id' => 555,
            'picture_url' => 'https://example.com/pic.png',
        ]);
        UserVariantFactory::createOne(['user' => $author, 'language' => $language, 'name' => 'Jane Doe']);

        $this->consoleBlogApi('GET', $blog, '/users/resolve-author?hyvor_user_id=555', [], user: $requester);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('Jane Doe', $json['name']);
        $this->assertSame('https://example.com/pic.png', $json['picture_url']);
    }
}
