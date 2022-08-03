<?php

namespace App\Domains\Cache\Listeners;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Cache\CacheRepository;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\Events\PostDeletedEvent;
use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\PostRepository;
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

class ClearTemplateCacheSubscriber
{
    private function clear(Blog $blog)
    {
        $cache = app(CacheRepository::class);
        $cache->blog($blog)->clearTemplateCache();
    }

    public function subscribe(Dispatcher $events)
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

    public function onPostUpdate(PostUpdatedEvent $event)
    {
        $post = $event->post;
        $blog = $post->blog;

        $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);
        $primaryVariant = PostRepository::getPostVariantByPostIdAndLanguageId($post->id, $primaryLanguage->id);

        if ($primaryVariant->status !== PostStatusEnum::PUBLISHED) {
            return;
        }

        $this->clear($blog);
    }

    public function onPostDelete(PostDeletedEvent $event)
    {
        $this->clear($event->post->blog);
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
            $this->clear($blog);
        }
    }

    public function onPostVariantDelete(PostVariantDeletedEvent $event)
    {
        $this->clear($event->variant->post->blog);
    }

    public function onUserEvent(UserCreatedEvent|UserUpdatedEvent|UserDeletedEvent $event)
    {
        $this->clear($event->user->blog);
    }

    public function onUserVariantEvent(UserVariantUpdatedEvent|UserVariantDeletedEvent $event)
    {
        $this->clear($event->variant->user->blog);
    }

    public function onTagEvent(TagCreatedEvent|TagUpdatedEvent|TagDeletedEvent $event)
    {
        $this->clear($event->tag->blog);
    }

    public function onTagVariantEvent(TagVariantUpdatedEvent|TagVariantDeletedEvent $event)
    {
        $this->clear($event->variant->tag->blog);
    }
}
