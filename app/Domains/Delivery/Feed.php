<?php
namespace App\Domains\BlogTheme;

use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\PostRepository;
use App\Models\Blog;

class Feed {

    public static function generateFeed(Blog $blog, $filter) {

        /**
         * Get only last 15 posts
         */
        $limit = 15;

        $posts = PostRepository::getPostsWithFilterQ(
            blogId: $blog->id,
            filter: $filter,
            limit: $limit,
            offset: 0,
            orderBy: 'published_at',
            orderMethod: 'DESC'
        )->map(function($post) use ($blog) {
            return new PostObject($post, $blog);
        });

        $vars = [
            '_blog' => new BlogObject($blog),
            '_posts' => $posts
        ];

        return TwigRenderer::renderFile(resource_path('twig/_feed.twig'), $vars);

    }

}