<?php

use App\Models\Blog;
use App\Models\Webhook;

function createWebhookFor(Blog $blog, string $eventName)
{
    Webhook::factory()->create([
        'blog_id' => $blog,
        'events' => [$eventName]
    ]);
}
