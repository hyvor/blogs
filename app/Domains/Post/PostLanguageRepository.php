<?php
namespace App\Domains\Post;

use App\Models\Post;

class PostLanguageRepository {

    public static function getPrimaryPost(Post $post) {

    }

    /**
     * Return variant posts 
     */
    public static function getVariants(Post $post) {

        return Post::where('blog_id', $post->blog_id)
            ->where('slug', $post->slug)
            ->withOnly('language')
            ->where('id', '!=', $post->id)
            ->get();

    }

}