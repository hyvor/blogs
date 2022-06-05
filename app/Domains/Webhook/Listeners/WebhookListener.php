<?php

namespace App\Domains\Webhook\Listeners;

use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Post\Events\PostPublishedEvent;

class ClearPostCacheListener
{
    public function handle($event)
    {
    }

    public function handleCacheShouldClearEvent(CacheClearSingleEvent $event)
    {
    }

    public function subscribe($events)
    {

        // post
        $events->listen(PostPublishedEvent::class, [$this, 'handlePostPublished']);

        // cache
        $events->listen(CacheClearSingleEvent::class, [$this, 'handleCacheShouldClearEvent']);
    }
}
