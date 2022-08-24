<?php

namespace App\Domains\Webhook\Listeners;

use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Models\Blog;
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
        // TODO:
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