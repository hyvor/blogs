<?php

namespace App\Domains\Shared\Count;

use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Models\Blog;
use Illuminate\Events\Dispatcher;

class CountSubscriber
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(PostCreatedEvent::class, [static::class, 'onPostCreateOrDelete']);
        $events->listen(PostDeletedEvent::class, [static::class, 'onPostCreateOrDelete']);
        $events->listen(PostVariantUpdatedEvent::class, [static::class, 'onPostVariantUpdate']);
    }

    public function onPostCreateOrDelete(PostCreatedEvent|PostDeletedEvent $event)
    {
        $blog = $event->post->blog;
        $this->dispatch($blog);
    }

    public function onPostVariantUpdate(PostVariantUpdatedEvent $event)
    {
        if ($event->variant->status !== $event->variantOld->status) {
            $this->dispatch($event->variant->post->blog);
        }
    }

    private function dispatch(Blog $blog)
    {
        BlogCountsJob::dispatch($blog);
        AuthorCountsJob::dispatch($blog);
        TagCountsJob::dispatch($blog);
    }
}
