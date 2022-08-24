<?php

use App\Models\Webhook;

function createWebhookFor(string $eventName) {
    Webhook::factory()->create([
        'blog_id' => blog(),
        'events' => [$eventName]
    ]);
}