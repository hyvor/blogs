<?php

namespace App\Domains\Cache\Listeners;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Cache\CacheService;
use App\Domains\Language\LanguageRepository;
use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Media\Events\MediaDeletedEvent;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\PostRepository;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Tag\Events\TagCreatedEvent;
use App\Domains\Tag\Events\TagDeletedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Domains\Tag\Events\TagVariantDeletedEvent;
use App\Domains\Tag\Events\TagVariantUpdatedEvent;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserUpdatedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Domains\User\Events\UserVariantUpdatedEvent;
use App\Models\Blog;
use Illuminate\Events\Dispatcher;

class ClearCacheSubscriber
{

    private function clearTemplateCache(Blog $blog): void
    {
        $cache = app(CacheService::class);
        $cache->blog($blog)->clearTemplateCache();
    }

    private function clearSingleCache(Blog $blog, string $path) : void
    {
        $cache = app(CacheService::class);
        $cache->blog($blog)->clearSingleCache($path);
    }

    public function subscribe(Dispatcher $events): void
    {
        $this->subscribeTemplateEvents($events);
        $this->subscribeSingleEvents($events);
        $this->subscribeAllEvents($events);
    }


    private function subscribeTemplateEvents(Dispatcher $events): void
    {
        $events->listen(PostUpdatedEvent::class, [static::class, 'onPostUpdate']);
        $events->listen(PostDeletedEvent::class, [static::class, 'onPostDelete']);
        $events->listen(PostVariantUpdatedEvent::class, [static::class, 'onPostVariantUpdate']);
        $events->listen(PostVariantDeletedEvent::class, [static::class, 'onPostVariantDelete']);

        $events->listen(UserCreatedEvent::class, [static::class, 'onUserEvent']);
        $events->listen(UserUpdatedEvent::class, [static::class, 'onUserEvent']);
        $events->listen(UserDeletedEvent::class, [static::class, 'onUserEvent']);
        $events->listen(UserVariantUpdatedEvent::class, [static::class, 'onUserVariantEvent']);
        $events->listen(UserVariantDeletedEvent::class, [static::class, 'onUserVariantEvent']);

        $events->listen(TagCreatedEvent::class, [static::class, 'onTagEvent']);
        $events->listen(TagUpdatedEvent::class, [static::class, 'onTagEvent']);
        $events->listen(TagDeletedEvent::class, [static::class, 'onTagEvent']);
        $events->listen(TagVariantUpdatedEvent::class, [static::class, 'onTagVariantEvent']);
        $events->listen(TagVariantDeletedEvent::class, [static::class, 'onTagVariantEvent']);
    }

    private function subscribeSingleEvents(Dispatcher $events) : void
    {
        $events->listen(MediaCreatedEvent::class, [static::class, 'onMediaEvent']);
        $events->listen(MediaDeletedEvent::class, [static::class, 'onMediaEvent']);
    }

    private function subscribeAllEvents(Dispatcher $event) : void
    {
        
    }


    public function onPostUpdate(PostUpdatedEvent $event)
    {
        $post = $event->post;
        $blog = $post->blog;

        $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);
        $primaryVariant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $primaryLanguage->id);

        if ($primaryVariant->status !== PostStatusEnum::PUBLISHED) {
            return;
        }

        $this->clearTemplateCache($blog);
    }

    public function onPostDelete(PostDeletedEvent $event)
    {
        $this->clearTemplateCache($event->post->blog);
    }

    public function onPostVariantUpdate(PostVariantUpdatedEvent $event)
    {
        $variant = $event->variant;
        $blog = $variant->post->blog;
        $variantOld = $event->variantOld;

        /**
         * Clear
         *  - if the status is changed
         *  - Variant status is published (which means data is changed)
         */
        if (
            $variantOld->status !== $variant->status ||
            $variant->status === PostStatusEnum::PUBLISHED
        ) {
            $this->clearTemplateCache($blog);
        }
    }

    public function onPostVariantDelete(PostVariantDeletedEvent $event)
    {
        $this->clearTemplateCache($event->variant->post->blog);
    }

    public function onUserEvent(UserCreatedEvent|UserUpdatedEvent|UserDeletedEvent $event)
    {
        $this->clearTemplateCache($event->user->blog);
    }

    public function onUserVariantEvent(UserVariantUpdatedEvent|UserVariantDeletedEvent $event)
    {
        $this->clearTemplateCache($event->variant->user->blog);
    }

    public function onTagEvent(TagCreatedEvent|TagUpdatedEvent|TagDeletedEvent $event)
    {
        $this->clearTemplateCache($event->tag->blog);
    }

    public function onTagVariantEvent(TagVariantUpdatedEvent|TagVariantDeletedEvent $event)
    {
        $this->clearTemplateCache($event->variant->tag->blog);
    }

    public function onMediaEvent(MediaCreatedEvent|MediaDeletedEvent $event)
    {
        $media = $event->media;
        $blog = $media->blog;

        $path = PermalinkRepository::getMediaPermalink($media, $blog, true);
        $this->clearSingleCache($blog, $path);
    }
}
