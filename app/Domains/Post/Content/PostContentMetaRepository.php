<?php

namespace App\Domains\Post\Content;

use App\Models\PostVariant;

/**
 * Manages meta counts (words)
 */
class PostContentMetaRepository
{
    /*public static function updateWordCount(PostVariant $variant): void
    {
        if (! $variant->content) {
            return;
        }

        $post = $variant->post;
        $blog = $post->blog;
        $text = PostContentRepository::getText($variant->content, $blog);

        $words = str_word_count($text);

        $variant->words = $words;
        $variant->saveQuietly();
    }*/

    /**
     * Pre-calculate HTML so that you don't want to convert it everytime
     *
     * @param  PostVariant  $variant
     * @return void
     */
    public static function updateHtmlContent(PostVariant $variant): void
    {
        if (! $variant->content) {
            return;
        }

        $post = $variant->post;
        $blog = $post->blog;
        $html = PostContentRepository::getHtml($variant->content, $blog);

        $variant->content_html = $html;
        $variant->saveQuietly();
    }
}
