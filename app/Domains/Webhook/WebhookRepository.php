<?php

namespace App\Domains\Webhook;

use App\Models\Blog;
use App\Models\Webhook;

class WebhookRepository
{
    public static function getWebhooks(Blog $blog)
    {
        return $blog->webhooks;
    }

    public static function createWebhook(Blog $blog, string $url, array $events)
    {
        $blog->webhooks()->create([
            'url' => $url,
            'events' => $events,
        ]);
    }

    public static function updateWebhook(int $id, string $url, array $events)
    {
        Webhook::find($id)->update([
            'url' => $url,
            'events' => $events,
        ]);
    }

    public static function deleteWebhook(int $id)
    {
        Webhook::find($id)->delete();
    }
}
