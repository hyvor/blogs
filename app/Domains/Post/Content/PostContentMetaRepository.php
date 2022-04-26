<?php
namespace App\Domains\Post\Content;

use App\Models\PostVariant;

/**
 * Manages meta counts (words)
 */
class PostContentMetaRepository
{

    public static function updateWordCount(PostVariant $variant) : void
    {

        if (!$variant->content)
            return;

        $post = $variant->post;
        $blog = $post->blog;
        $text = PostContentRepository::getText($variant->content, $blog);

        $words = str_word_count($text);

        $variant->words = $words;
        $variant->saveQuietly();

    }

}