<?php

use App\Models\Blog;
use App\Models\Webhook;
use App\Data\Enums\WebhookEventEnum;

function createWebhookFor(Blog $blog, WebhookEventEnum $eventName)
{
    Webhook::factory()->create([
        'blog_id' => $blog,
        'events' => [$eventName]
    ]);
}
