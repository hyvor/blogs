<?php

namespace App\Domains\Cache\Listeners;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Cache\CacheRepository;
use App\Domains\Post\Events\PostVariantDeletedEvent;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Models\Blog;
use Illuminate\Events\Dispatcher;

class ClearTemplateCacheSubscriber
{

    private function clear(Blog $blog)
    {
        $cache = app(CacheRepository::class);
        $cache->blog($blog)->clearTemplateCache();
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

    public function subscribe(Dispatcher $events)
    {

        // $events->listen();
        $events->listen(PostVariantUpdatedEvent::class, [static::class, 'onPostVariantUpdate']);
        $events->listen(PostVariantDeletedEvent::class, [static::class, 'onPostVariantDelete']);

    }

}