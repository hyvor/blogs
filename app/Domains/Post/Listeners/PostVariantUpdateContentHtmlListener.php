<?php declare(strict_types=1);

namespace App\Domains\Post\Listeners;

use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Post\Events\PostVariantUpdatedEvent;

class PostVariantUpdateContentHtmlListener
{
    public function handle(PostVariantUpdatedEvent $event) : void
    {
        (new PostContentRepository)->updateVariantHtml($event->variant);
    }
}
