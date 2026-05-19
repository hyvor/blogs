<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
class CreateWebhookTest extends ApiTestCase
{
    public function test_create_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-create'],
            ['hyvor_user_id' => 101, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 101]);
        $this->consoleBlogApi('POST', 'wh-create', '/webhook', [
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
        for ($i = 0; $i < 5; $i++) {
            WebhookFactory::createOne([
                'blog' => $blog,
                'blog_id' => $blog->getId(),
            ]);
        }

        $authUser = AuthFake::generateUser(['id' => 102]);
        $this->consoleBlogApi('POST', 'wh-limit', '/webhook', [
            'url' => 'https://example.com/hook',
            'events' => ['post.created'],
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }
}
