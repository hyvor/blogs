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
    public function testGetWebhooksDeliveries(): void
    {
        $blog = BlogFactory::withAccess();

        $webhook = Webhook::factory()->create(['blog_id' => $blog]);
        WebhookDelivery::factory()->create(['webhook_id' => $webhook]);

        $response = $this->consoleApi(
            $blog,
            'GET',
            '/webhook-deliveries',
        )->assertOk();
        dd($response->json());
        $this->assertCount(2, $response->json());
    }
}
