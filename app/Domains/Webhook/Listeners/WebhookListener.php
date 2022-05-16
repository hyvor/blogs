<?php

namespace App\Domains\Webhook\Listeners;

use App\Domains\Cache\Events\CacheShouldClearEvent;
use App\Domains\Post\Events\PostPublishedEvent;

class ClearPostCacheListener
{
    public function handle($event)
    {
    }

    public function handleCacheShouldClearEvent(CacheShouldClearEvent $event)
    {
    }

    public function subscribe($events)
    {

        // post
        $events->listen(PostPublishedEvent::class, [$this, 'handlePostPublished']);

        // cache
        $events->listen(CacheShouldClearEvent::class, [$this, 'handleCacheShouldClearEvent']);
    }
}
