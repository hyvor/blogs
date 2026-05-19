<?php

namespace Api\Console\Blog;

use App\Api\Console\Controller\WebhookController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookDeliveryFactory;
use App\Tests\Factory\WebhookFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
class WebhookTest extends ApiTestCase
{
    public function test_get_webhooks(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-test'],
            ['hyvor_user_id' => 100, 'status' => 'active'],
        );
        WebhookFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => 'https://example.com/hook',
            'events' => ['post.created'],
            'secret' => 'mysecret1234567890123456789012',
        ]);

        $authUser = AuthFake::generateUser(['id' => 100]);
        $this->consoleBlogApi('GET', 'wh-test', '/webhooks', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json);
        $this->assertCount(1, $json);
        $this->assertSame('https://example.com/hook', $json[0]['url']);
        $this->assertSame(['post.created'], $json[0]['events']);
        $this->assertArrayHasKey('secret', $json[0]);
    }

    public function test_create_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-create'],
            ['hyvor_user_id' => 101, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 101]);
        $this->consoleBlogApi('POST', 'wh-create', '/webhooks', [
            'url' => 'https://example.com/hook',
            'events' => ['post.created', 'post.updated'],
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('https://example.com/hook', $json['url']);
        $this->assertSame(['post.created', 'post.updated'], $json['events']);
        $this->assertArrayHasKey('secret', $json);
    }

    public function test_create_webhook_limit(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-limit'],
            ['hyvor_user_id' => 102, 'status' => 'active'],
        );
        // create 5 webhooks (the limit)
        for ($i = 0; $i < 5; $i++) {
            WebhookFactory::createOne([
                'blog' => $blog,
                'blog_id' => $blog->getId(),
            ]);
        }

        $authUser = AuthFake::generateUser(['id' => 102]);
        $this->consoleBlogApi('POST', 'wh-limit', '/webhooks', [
            'url' => 'https://example.com/hook',
            'events' => ['post.created'],
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_update_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-update'],
            ['hyvor_user_id' => 103, 'status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => 'https://old.com/hook',
            'events' => ['post.created'],
        ]);

        $authUser = AuthFake::generateUser(['id' => 103]);
        $this->consoleBlogApi('PATCH', 'wh-update', '/webhooks/' . $webhook->getId(), [
            'url' => 'https://new.com/hook',
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('https://new.com/hook', $json['url']);
    }

    public function test_update_webhook_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-upd-b1'],
            ['hyvor_user_id' => 104, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-upd-b2'],
            ['hyvor_user_id' => 105, 'status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 104]);
        $this->consoleBlogApi('PATCH', 'wh-upd-b1', '/webhooks/' . $webhook->getId(), [
            'url' => 'https://hack.com/hook',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_delete_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-delete'],
            ['hyvor_user_id' => 106, 'status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 106]);
        $this->consoleBlogApi('DELETE', 'wh-delete', '/webhooks/' . $webhook->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
    }

    public function test_delete_webhook_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-del-b1'],
            ['hyvor_user_id' => 107, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-del-b2'],
            ['hyvor_user_id' => 108, 'status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 107]);
        $this->consoleBlogApi('DELETE', 'wh-del-b1', '/webhooks/' . $webhook->getId(), user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_get_webhook_deliveries(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-deliveries'],
            ['hyvor_user_id' => 109, 'status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);
        WebhookDeliveryFactory::createOne([
            'webhook' => $webhook,
            'webhook_id' => $webhook->getId(),
            'event' => 'post.created',
            'status' => 'success',
            'url' => 'https://example.com/hook',
        ]);

        $authUser = AuthFake::generateUser(['id' => 109]);
        $this->consoleBlogApi('GET', 'wh-deliveries', '/webhook-deliveries', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json);
        $this->assertCount(1, $json);
        $this->assertSame('post.created', $json[0]['event']);
        $this->assertSame('success', $json[0]['status']);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-denied'],
            ['hyvor_user_id' => 110, 'status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'wh-denied', '/webhooks', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_blog_not_found(): void
    {
        $authUser = AuthFake::generateUser(['id' => 111]);
        $this->consoleBlogApi('GET', 'nonexistent-blog-xyz', '/webhooks', user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }
}
