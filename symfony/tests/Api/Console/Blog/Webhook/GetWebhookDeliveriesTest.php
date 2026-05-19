<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookDeliveryFactory;
use App\Tests\Factory\WebhookFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
class GetWebhookDeliveriesTest extends ApiTestCase
{
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
        $this->assertIsArray($json[0]);
        $this->assertSame('post.created', $json[0]['event']);
        $this->assertSame('success', $json[0]['status']);
    }
}
