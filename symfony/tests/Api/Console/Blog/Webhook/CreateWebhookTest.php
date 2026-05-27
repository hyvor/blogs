<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
class CreateWebhookTest extends ApiTestCase
{
    public function test_create_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-create'],
            ['status' => 'active'],
        );

        $this->consoleBlogApi('POST', 'wh-create', '/webhook', [
            'url' => 'https://example.com/hook',
            'events' => ['post.created', 'post.updated'],
        ], user: $user);

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
            ['status' => 'active'],
        );
        for ($i = 0; $i < 5; $i++) {
            WebhookFactory::createOne([
                'blog' => $blog,
                'blog_id' => $blog->getId(),
            ]);
        }

        $this->consoleBlogApi('POST', 'wh-limit', '/webhook', [
            'url' => 'https://example.com/hook',
            'events' => ['post.created'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }
}
