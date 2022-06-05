<?php

namespace App\Domains\Post\Listeners;

use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Post\Events\PostVariantUpdatedEvent;

class PostVariantUpdateContentHtmlListener
{

    public function handle(PostVariantUpdatedEvent $event)
    {

        $variant = $event->variant;

        if (!$variant->content) {
            return;
        }

        $post = $variant->post;
        $blog = $post->blog;
        $html = PostContentRepository::getHtml($variant->content, $blog);

        $variant->content_html = $html;
        $variant->save();

    }

}