<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Api\Console\Object\WebhookDeliveryObject;
use App\Entity\Enum\UserStatus;
use App\Entity\Enum\WebhookDeliveryStatus;
use App\Entity\Enum\WebhookEvent;
use App\Service\Webhook\WebhookDeliveryService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookDeliveryFactory;
use App\Tests\Factory\WebhookFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
#[CoversClass(WebhookDeliveryObject::class)]
#[CoversClass(WebhookDeliveryService::class)]
class GetWebhookDeliveriesTest extends ApiTestCase
{
    public function test_get_webhook_deliveries(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-deliveries'],
            ['status' => UserStatus::ACTIVE],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
        ]);
        WebhookDeliveryFactory::createOne([
            'webhook' => $webhook,
            'webhook_id' => $webhook->getId(),
            'event' => WebhookEvent::POST_CREATED,
            'status' => WebhookDeliveryStatus::SUCCESS,
            'url' => 'https://example.com/hook',
        ]);

        $this->consoleBlogApi('GET', 'wh-deliveries', '/webhook-deliveries', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('post.created', $json[0]['event']);
        $this->assertSame('success', $json[0]['status']);
    }
}
