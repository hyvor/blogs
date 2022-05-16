<?php

namespace App\Domains\Cache\Listeners;

use App\Domains\Cache\Events\CacheShouldClearEvent;
use App\Domains\Post\Events\PostPublishedEvent;
use App\Domains\Route\PermalinkRepository;

class ClearPostCacheListener
{
    public function handle($event)
    {
        $post = $event->post;
        $path = PermalinkRepository::getPostPermalink($post, $post->blog, true);

        CacheShouldClearEvent::dispatch($post->blog, $path);
    }

    public function subscribe($events)
    {
        $events->listen(PostPublishedEvent::class, [$this, 'handle']);
    }
}
