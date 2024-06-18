<?php

namespace App\Domains\Webhook\Listeners;

use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Domains\Webhook\Jobs\WebhookDeliveryJob;
use App\Domains\Webhook\WebhookDeliveryService;
use App\Domains\Webhook\WebhookService;
use App\Models\Blog;
use App\Data\Enums\WebhookEventEnum;
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

    private function call(Blog $blog, WebhookEventEnum $eventName, array $data = [])
    {
        $webhooks = $blog->webhooks;

        foreach ($webhooks as $webhook) {
            if (in_array($eventName->value, $webhook->events)) {
                $delivery = WebhookDeliveryService::createDelivery($webhook, $eventName, $data);
                WebhookDeliveryJob::dispatch($delivery);
            }
        }
    }

    public function onCacheClearSingleEvent(CacheClearSingleEvent $event)
    {
        $this->call($event->blog, WebhookEventEnum::CACHE_SINGLE, [
            'path' => $event->path
        ]);
    }
    public function onCacheClearTemplatesEvent(CacheClearTemplatesEvent $event)
    {
        $this->call($event->blog, WebhookEventEnum::CACHE_TEMPLATES);
    }
    public function onCacheClearAllEvent(CacheClearAllEvent $event)
    {
        $this->call($event->blog, WebhookEventEnum::CACHE_ALL);
    }
}
