<?php declare(strict_types=1);

namespace App\Domains\PostHistory\Listeners;

use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\PostHistory\PostHistoryService;

class PostHistoryVariantUpdateListener
{

    public function handle(PostVariantUpdatedEvent $event) : void
    {

        if (
            $event->variant->content !== $event->variantOld->content &&
            $event->variant->content
        ) {
            PostHistoryService::createHistory($event->variant);
        }

    }

}