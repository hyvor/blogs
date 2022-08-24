<?php

namespace App\Domains\Webhook;

use App\Models\Blog;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Collection;

class WebhookService
{

    public const EVENTS = [

        // CACHE
        'cache.single',
        'cache.templates',
        'cache.all'
    ];

    public static function getWebhooks(Blog $blog) : Collection
    {
        return $blog->webhooks;
    }

    public static function getWebhooksCount(Blog $blog) : int
    {
        return $blog->webhooks()->count();
    }

    public static function createWebhook(Blog $blog, string $url, array $events) : Webhook
    {
        $secret = bin2hex(random_bytes(16));
        return $blog->webhooks()->create([
            'url' => $url,
            'events' => $events,
            'secret' => $secret
        ]);
    }

    public static function updateWebhook(Webhook $webhook, string $url = null, array $events = null) : Webhook
    {
        if ($url)
            $webhook->url = $url;

        if ($events)
            $webhook->events = $events;

        $webhook->save();

        return $webhook;
    }

    public static function deleteWebhook(Webhook $webhook)
    {
        $webhook->delete();
    }
}
