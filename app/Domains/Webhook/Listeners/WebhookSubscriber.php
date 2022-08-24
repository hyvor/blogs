<?php

namespace App\Domains\Webhook\Listeners;

use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Domains\Webhook\Jobs\WebhookDeliveryJob;
use App\Domains\Webhook\WebhookService;
use App\Models\Blog;
use Exception;
use Illuminate\Events\Dispatcher;

class WebhookSubscriber
{

    public function subscribe(Dispatcher $events)
    {

        $events->listen(CacheClearSingleEvent::class, [static::class, 'onCacheClearSingleEvent']);
        $events->listen(CacheClearTemplatesEvent::class, [static::class, 'onCacheClearTemplatesEvent']);
        $events->listen(CacheClearAllEvent::class, [static::class, 'onCacheClearAllEvent']);

    }

    private function call(Blog $blog, string $eventName, array $data = [])
    {

        if (!in_array($eventName, WebhookService::EVENTS)) // to be safe
            throw new Exception('Invalid webhook event name');

        $webhooks = $blog->webhooks;

        foreach ($webhooks as $webhook) {

            if (in_array($eventName, $webhook->events)) {
                WebhookDeliveryJob::dispatch($blog, $webhook, $eventName, $data);
            }

        }
    }

    public function onCacheClearSingleEvent(CacheClearSingleEvent $event)
    {
        $this->call($event->blog, 'cache.single', [
            'path' => $event->path
        ]);
    }
    public function onCacheClearTemplatesEvent(CacheClearTemplatesEvent $event)
    {
        $this->call($event->blog, 'cache.templates');
    }
    public function onCacheClearAllEvent(CacheClearAllEvent $event)
    {
        $this->call($event->blog, 'cache.all');
    }

}