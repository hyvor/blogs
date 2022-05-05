<?php

namespace App\Domains\Post;

use App\Models\Post;

class PostLanguageRepository
{
    public static function getPrimaryPost(Post $post)
    {
        if ($post->language->is_primary) {
            return $post;
        }

        return Post::where('posts.blog_id', $post->blog_id)
            ->where('posts.slug', $post->slug)
            ->join('languages', 'languages.id', '=', 'posts.language_id')
            ->where('languages.is_primary', true)
            ->first();
    }

    /**
     * Return variant posts
     */
    public static function getVariants(Post $post)
    {
        return Post::where('blog_id', $post->blog_id)
            ->where('slug', $post->slug)
            ->withOnly('language')
            ->where('id', '!=', $post->id)
            ->get();
    }
}
