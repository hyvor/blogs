<?php

namespace App\Domains\Shared\Count;

use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Models\Blog;
use Illuminate\Events\Dispatcher;

class CountSubscriber
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(PostCreatedEvent::class, [static::class, 'onPostCreateOrDelete']);
        $events->listen(PostDeletedEvent::class, [static::class, 'onPostCreateOrDelete']);
        $events->listen(PostVariantUpdatedEvent::class, [static::class, 'onPostVariantUpdate']);

        $events->listen(UserCreatedEvent::class, [static::class, 'onUserEvent']);
        $events->listen(UserDeletedEvent::class, [static::class, 'onUserEvent']);

        $events->listen(MediaCreatedEvent::class, [static::class, 'onMediaEvent']);
        $events->listen(MediaDeletedEvent::class, [static::class, 'onMediaEvent']);
    }

    public function onPostCreateOrDelete(PostCreatedEvent|PostDeletedEvent $event)
    {
        $blog = $event->post->blog;
        $this->dispatchPostCountJobs($blog);
    }

    public function onPostVariantUpdate(PostVariantUpdatedEvent $event)
    {
        if ($event->variant->status !== $event->variantOld->status) {
            $this->dispatchPostCountJobs($event->variant->post->blog);
        }
    }

    public function onUserEvent(UserCreatedEvent | UserDeletedEvent $event)
    {
        $blog = $event->user->blog;
        BlogUsersCountsJob::dispatch($blog);
    }

    public function onMediaEvent(MediaCreatedEvent | MediaDeletedEvent $event)
    {
        $blog = $event->media->blog;
        BlogMediaCountsJob::dispatch($blog);
    }

    private function dispatchPostCountJobs(Blog $blog)
    {
        BlogPostsCountsJob::dispatch($blog);
        AuthorCountsJob::dispatch($blog);
        TagCountsJob::dispatch($blog);
    }
}
