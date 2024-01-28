<?php

namespace App\Domains\Post\Listeners;

use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\Events\PostVariantUpdatedEvent;

class PostVariantUpdateWordCountListener
{
    public function handle(PostVariantUpdatedEvent $event)
    {
        $variant = $event->variant;

        if (! $variant->content) {
            return;
        }

        $post = $variant->post;
        $blog = $post->blog;
        $text = PostContentService::getText($variant->content, $blog);

        $words = str_word_count($text);

        $variant->words = $words;
        $variant->save();
    }
}
