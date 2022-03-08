<?php
namespace App\Domains\BlogTheme;

use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Domains\BlogTheme\Twig\Renderer;
use App\Domains\Post\PostRepository;
use App\Models\Blog;

class BlogThemeFeedRepository {

    public static function generateFeed(Blog $blog, $filter) {

        /**
         * Get only last 15 posts
         */
        $limit = 15;

        $vars = [
            '_blog' => new BlogObject($blog),
            '_posts' => PostRepository::getPostsWithFilterQ(
                $blog->id, $filter,
                $limit,
                0,
                'published_at',
                'DESC'
            )->map(function($post) use ($blog) {
                return new PostObject($post, $blog);
            })
        ];

        return Renderer::renderFile(resource_path('twig/_feed.twig'), $vars);

    }

}