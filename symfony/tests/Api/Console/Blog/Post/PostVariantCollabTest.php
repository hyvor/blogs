<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Api\Console\Controller\PostVariantCollabController;
use App\Entity\Enum\PostVariantContentType;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Entity\PostVariantStep;
use App\Service\Post\Collab\PostVariantCollabService;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use App\Tests\Fake\FakeHub;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostVariantCollabController::class)]
#[CoversClass(PostVariantCollabService::class)]
#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
class PostVariantCollabTest extends ApiTestCase
{
    /** @return array<string, mixed> */
    private function step(string $marker): array
    {
        return ['stepType' => 'test', 'marker' => $marker];
    }

    /**
     * Seeds a variant as if a step batch had already been accepted, without a second
     * consoleBlogApi() call (only one is allowed per test - see ApiTestCase/AuthFake).
     */
    private function seedStep(PostVariant $variant, PostVariantContentType $type, string $clientId): void
    {
        $variant->setVersion($type, $variant->getVersion($type) + 1);
        $row = (new PostVariantStep())
            ->setPostVariant($variant)
            ->setType($type)
            ->setVersion($variant->getVersion($type))
            ->setClientId($clientId)
            ->setStep($this->step($clientId))
            ->setCreatedAt(new \DateTimeImmutable());
        $this->getEm()->persist($row);
        $this->getEm()->flush();
    }

    public function test_accepts_steps_bumps_version_and_publishes_to_mercure(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'collab-accept']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/collab', [
            'language_id' => $language->getId(),
            'type' => 'content',
            'version' => 0,
            'steps' => [$this->step('a'), $this->step('b')],
            'client_id' => 'client-1',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['accepted']);

        $this->getEm()->clear();
        /** @var PostVariant $refreshed */
        $refreshed = $this->getEm()->getRepository(PostVariant::class)->find($variant->getId());
        $this->assertSame(2, $refreshed->getContentVersion());

        /** @var FakeHub $hub */
        $hub = $this->getContainer()->get(FakeHub::class);
        $update = $hub->assertPublished('post_variant_collab:' . $variant->getId() . ':content');
        /** @var array<string, mixed> $payload */
        $payload = json_decode($update->getData(), true);
        /** @var array<int, mixed> $steps */
        $steps = $payload['steps'];
        $this->assertSame(2, $payload['version']);
        $this->assertCount(2, $steps);
        $this->assertSame(['client-1', 'client-1'], $payload['client_ids']);
    }

    public function test_rejects_stale_version_without_error(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'collab-stale']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/collab', [
            'language_id' => $language->getId(),
            'type' => 'content',
            'version' => 5, // stale - live version is 0
            'steps' => [$this->step('a')],
            'client_id' => 'client-1',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertFalse($json['accepted']);

        $this->getEm()->clear();
        /** @var PostVariant $refreshed */
        $refreshed = $this->getEm()->getRepository(PostVariant::class)->find($variant->getId());
        $this->assertSame(0, $refreshed->getContentVersion());

        /** @var FakeHub $hub */
        $hub = $this->getContainer()->get(FakeHub::class);
        $hub->assertNotPublished('post_variant_collab:' . $variant->getId() . ':content');
    }

    public function test_checkpoint_persists_content_and_prunes_steps(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'collab-checkpoint']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $postId = $post->getId();
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT, 'content' => null]);
        $variantId = $variant->getId();
        $this->seedStep($variant, PostVariantContentType::CONTENT, 'client-1');

        $doc = json_encode(['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => []]]]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $postId . '/variant/collab/checkpoint', [
            'language_id' => $language->getId(),
            'type' => 'content',
            'version' => 1,
            'content' => $doc,
        ], user: $user);
        $this->assertResponseIsSuccessful();

        $this->getEm()->clear();
        /** @var PostVariant $refreshed */
        $refreshed = $this->getEm()->getRepository(PostVariant::class)->find($variantId);
        $this->assertSame($doc, $refreshed->getContent());
        $this->assertSame(1, $refreshed->getContentVersion());

        /** @var string|int $stepCount */
        $stepCount = $this->getEm()->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM post_variant_steps WHERE post_variant_id = ?',
            [$variantId],
        );
        $this->assertSame(0, (int) $stepCount);
    }

    public function test_clearing_content_unsaved_resets_its_collab_stream(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'collab-reset']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'content' => json_encode(['type' => 'doc', 'content' => []]),
            'content_unsaved' => json_encode(['type' => 'doc', 'content' => []]),
        ]);
        $variantId = $variant->getId();
        $this->seedStep($variant, PostVariantContentType::CONTENT_UNSAVED, 'client-1');
        $this->assertSame(1, $variant->getContentUnsavedVersion());

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
            'content_unsaved' => null,
        ], user: $user);
        $this->assertResponseIsSuccessful();

        $this->getEm()->clear();
        /** @var PostVariant $refreshed */
        $refreshed = $this->getEm()->getRepository(PostVariant::class)->find($variantId);
        $this->assertSame(0, $refreshed->getContentUnsavedVersion());

        /** @var string|int $stepCount */
        $stepCount = $this->getEm()->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM post_variant_steps WHERE post_variant_id = ?',
            [$variantId],
        );
        $this->assertSame(0, (int) $stepCount);
    }

    public function test_checkpoint_conflicts_on_stale_version(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'collab-checkpoint-conflict']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);

        $doc = json_encode(['type' => 'doc', 'content' => []]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/collab/checkpoint', [
            'language_id' => $language->getId(),
            'type' => 'content',
            'version' => 3, // stale - live version is 0
            'content' => $doc,
        ], user: $user);

        $this->assertResponseFailed(409);
    }

    public function test_submits_cursor_and_publishes_resolved_user_to_mercure(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'collab-cursor']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $user = UserFactory::createOne(['blog' => $blog, 'cursor_color' => 'hsl(210, 70%, 35%)']);
        UserVariantFactory::createOne(['user' => $user, 'language' => $language, 'name' => 'Cursor Author']);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/collab/cursor', [
            'language_id' => $language->getId(),
            'type' => 'content',
            'client_id' => 'client-1',
            'from' => 3,
            'to' => 7,
        ], user: $user);

        $this->assertResponseIsSuccessful();

        /** @var FakeHub $hub */
        $hub = $this->getContainer()->get(FakeHub::class);
        $update = $hub->assertPublished('post_variant_collab:' . $variant->getId() . ':content');
        /** @var array<string, mixed> $payload */
        $payload = json_decode($update->getData(), true);

        $this->assertSame('cursor', $payload['type']);
        $this->assertSame('client-1', $payload['client_id']);
        $this->assertSame(3, $payload['from']);
        $this->assertSame(7, $payload['to']);
        $this->assertArrayNotHasKey('clear', $payload);
        /** @var array<string, mixed> $userPayload */
        $userPayload = $payload['user'];
        $this->assertSame('Cursor Author', $userPayload['name']);
        $this->assertSame('hsl(210, 70%, 35%)', $userPayload['color']);
    }

    public function test_submits_cursor_clear_on_blur(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'collab-cursor-clear']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/collab/cursor', [
            'language_id' => $language->getId(),
            'type' => 'content',
            'client_id' => 'client-1',
            'from' => null,
            'to' => null,
        ], user: $user);

        $this->assertResponseIsSuccessful();

        /** @var FakeHub $hub */
        $hub = $this->getContainer()->get(FakeHub::class);
        $update = $hub->assertPublished('post_variant_collab:' . $variant->getId() . ':content');
        /** @var array<string, mixed> $payload */
        $payload = json_decode($update->getData(), true);

        $this->assertSame('cursor', $payload['type']);
        $this->assertTrue($payload['clear']);
        $this->assertArrayNotHasKey('from', $payload);
        $this->assertArrayNotHasKey('user', $payload);
    }

    public function test_get_post_returns_collab_state_and_sets_subscription_cookie(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'collab-get']);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'status' => PostVariantStatus::DRAFT]);
        $this->seedStep($variant, PostVariantContentType::CONTENT, 'client-1');

        $response = $this->consoleBlogApi('GET', $blog, '/post/' . $post->getId() . '?variant_language_code=' . $language->getCode(), user: $user);
        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        /** @var array<string, mixed> $variantJson */
        $variantJson = $json['variant'];
        /** @var array<int, mixed> $contentSteps */
        $contentSteps = $variantJson['content_steps'];

        $this->assertSame(1, $variantJson['content_version']);
        $this->assertCount(1, $contentSteps);
        $this->assertSame(['client-1'], $variantJson['content_client_ids']);
        $this->assertSame(0, $variantJson['content_unsaved_version']);

        $cookies = $response->headers->getCookies();
        $names = array_map(fn($c) => $c->getName(), $cookies);
        $this->assertContains('mercureAuthorization', $names);
    }
}
