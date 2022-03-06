<?php
namespace App\Domains\Route;

use App\Models\Blog;

class RouteRepository {

    public static function addDefaultRoutes(Blog $blog) {

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
                'template' => 'index',
                'posts_filter' => ''
            ],
            // tag
            [
                'name' => 'tag',
                'match' => '/tag/{slug}',
                'template' => 'tag,index',
                'posts_filter' => 'tag.slug = {slug}',
            ],
            // author
            [
                'name' => 'author',
                'match' => '/author/{slug}',
                'template' => 'author,index',
                'posts_filter' => 'author.slug = {slug}'
            ],
            // search
            [
                'name' => 'search',
                'match' => '/search',
            ]
        ]);

    }

    public static function getRoute(Blog $blog, string $name) {

        /**
         * Even calling ->routes fetches all routes
         * it fetches the relationship, so it prevents calling more duplicate queries from 
         * the Blog model's route relationship
         */
        $routes = $blog->routes;

        return $routes->where('name', $name)->first();

    }

}