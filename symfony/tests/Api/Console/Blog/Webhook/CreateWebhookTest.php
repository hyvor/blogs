<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Entity\Enum\WebhookEvent;
use App\Entity\Webhook;
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

        $webhooks = $this->getEm()->getRepository(Webhook::class)->findAll();
        $this->assertCount(1, $webhooks);
        $webhook = $webhooks[0];
        $this->assertSame($blog->getId(), $webhook->getBlogId());
        $this->assertSame('https://example.com/hook', $webhook->getUrl());
        $this->assertSame([WebhookEvent::POST_CREATED, WebhookEvent::POST_UPDATED], $webhook->getEvents());
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

    public function test_fails_with_wrong_event_name(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-wrong-event'],
            ['status' => 'active'],
        );

        $this->consoleBlogApi('POST', 'wh-wrong-event', '/webhook', [
            'url' => 'https://example.com/hook',
            'events' => ['invalid.event'],
        ], user: $user);

        $this->assertResponseFailed(422, 'events[0]:');
    }
}
