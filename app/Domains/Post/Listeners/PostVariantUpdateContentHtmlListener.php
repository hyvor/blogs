<?php

namespace App\Domains\Post\Listeners;

use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Post\Events\PostVariantUpdatedEvent;

class PostVariantUpdateContentHtmlListener
{
    public function handle(PostVariantUpdatedEvent $event)
    {
        PostContentRepository::updateVariantHtml($event->variant);
    }
}
