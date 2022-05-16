<?php

namespace App\Domains\Delivery;

use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\PostRepository;
use App\Models\Blog;
use App\Models\Language;

class Feed
{
    public static function generateFeed(Blog $blog, Language $language, string $filter): string
    {

        /**
         * Get only last 25 posts
         */
        $limit = 25;

        $posts = PostRepository::getPostsWithFilterQ(
            blog: $blog,
            language: $language,
            filter: $filter,
            limit: $limit,
        )->collection->map(fn ($post) => new PostObject($post, $blog, $language));

        $vars = [
            '_blog' => new BlogObject($blog, $language),
            '_posts' => $posts,
        ];

        return TwigRenderer::renderFile(resource_path('twig/_feed.twig'), $vars);
    }
}
