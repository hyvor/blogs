<?php
namespace App\Domains\Route;

use App\Models\Blog;

class RouteRepository {

    static function addDefaultRoutes(Blog $blog) {

        $blog->routes()->createMany([
            // post
            [
                'name' => 'post',
                'match' => '/{tag}/{slug}'
            ],
            // page
            [
                'name' => 'page',
                'match' => '/{slug}'
            ],
            // home page (index)
            [
                'name' => 'index',
                'match' => '/',
                'posts_filter' => ''
            ],
            // tag
            [
                'name' => 'tag',
                'match' => '/tag/{slug}',
                'posts_filter' => 'tag.slug = {slug}',
            ],
            // author
            [
                'name' => 'author',
                'match' => '/author/{slug}',
                'posts_filter' => 'author.slug = {slug}'
            ],
            // search
            [
                'name' => 'search',
                'match' => '/search',
            ]
        ]);

    }


}