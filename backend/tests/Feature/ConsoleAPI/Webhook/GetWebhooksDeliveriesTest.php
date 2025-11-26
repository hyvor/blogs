<?php

namespace Tests\Feature\ConsoleAPI\Webhook;

use App\Models\Blog;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Database\Factories\BlogFactory;
use Tests\Case\AppTestCase;
use Tests\Case\DatabaseTestCase;

class GetWebhooksDeliveriesTest extends DatabaseTestCase
{
    public function testGetAllWebhooksDeliveries(): void
    {
        $blog = BlogFactory::withAccess();
        $webhook = Webhook::factory()->create(['blog_id' => $blog]);
        WebhookDelivery::factory()->create(['webhook_id' => $webhook]);
        WebhookDelivery::factory()->create(['webhook_id' => $webhook]);

        $response = $this->consoleApi(
            $blog,
            'GET',
            '/webhook-deliveries',
        )->assertOk();

        $this->assertCount(2, $response->json());
    }

    public function testGetSpecificWebhookDeliveries(): void
    {
        $blog = BlogFactory::withAccess();
        $webhook1 = Webhook::factory()->create(['blog_id' => $blog]);
        $webhook2 = Webhook::factory()->create(['blog_id' => $blog]);
        WebhookDelivery::factory()->create(['webhook_id' => $webhook1]);
        WebhookDelivery::factory()->create(['webhook_id' => $webhook1]);
        WebhookDelivery::factory()->create(['webhook_id' => $webhook2]);

        $response = $this->consoleApi(
            $blog,
            'GET',
            '/webhook-deliveries?webhook_id=' . $webhook1->id,
        )->assertOk();

        $this->assertCount(2, $response->json());
    }
}
