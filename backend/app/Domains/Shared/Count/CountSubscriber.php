<?php

namespace App\Domains\Shared\Count;

use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Models\Blog;
use Illuminate\Events\Dispatcher;

class CountSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(PostCreatedEvent::class, [static::class, 'onPostCreateOrDelete']);
        $events->listen(PostDeletedEvent::class, [static::class, 'onPostCreateOrDelete']);
        $events->listen(PostUpdatedEvent::class, [static::class, 'onPostUpdate']);
        $events->listen(PostVariantUpdatedEvent::class, [static::class, 'onPostVariantUpdate']);

        $events->listen(UserCreatedEvent::class, [static::class, 'onUserEvent']);
        $events->listen(UserDeletedEvent::class, [static::class, 'onUserEvent']);

        $events->listen(MediaCreatedEvent::class, [static::class, 'onMediaEvent']);
        $events->listen(MediaDeletedEvent::class, [static::class, 'onMediaEvent']);
    }

    public function onPostCreateOrDelete(PostCreatedEvent|PostDeletedEvent $event): void
    {
        $blog = $event->post->blog;
        if ($blog) {
            $this->dispatchPostCountJobs($blog);
        }
    }

    public function onPostUpdate(PostUpdatedEvent $event): void
    {
        $blog = $event->post->blog;
        if ($blog && ($event->post->is_featured !== $event->postOld->is_featured)) {
            $this->dispatchPostCountJobs($blog);
        }
    }

    public function onPostVariantUpdate(PostVariantUpdatedEvent $event): void
    {
        $post = $event->variant->post;
        $blog = $post->blog;
        if ($blog && ($event->variant->status !== $event->variantOld->status)) {
            $this->dispatchPostCountJobs($blog);
        }
    }

    public function onUserEvent(UserCreatedEvent|UserDeletedEvent $event): void
    {
        $blog = $event->user->blog;
        if ($blog) {
            BlogUsersCountsJob::dispatch($blog);
        }
    }

    public function onMediaEvent(MediaCreatedEvent|MediaDeletedEvent $event): void
    {
        $blog = $event->media->blog;
        if ($blog) {
            BlogMediaCountsJob::dispatch($blog);
        }
    }

    private function dispatchPostCountJobs(Blog $blog): void
    {
        BlogPostsCountsJob::dispatch($blog);
        AuthorCountsJob::dispatch($blog);
        TagCountsJob::dispatch($blog);
    }
}
