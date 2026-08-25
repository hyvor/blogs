<?php

declare(strict_types=1);

namespace App\Domains\Post\Listeners;

use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\PostRepository;

class PostVariantUpdateContentHtmlListener
{
    public function handle(PostVariantUpdatedEvent $event): void
    {
        // content has not changed
        if ($event->variant->content === $event->variantOld->content) {
            return;
        }

        PostRepository::updateVariantHtml($event->variant);
    }

    // should listen ot PostVariantCreatedEvent when status !== draft
}
