<?php
namespace App\Domains\Route;

use App\Exceptions\TrustedException;
use App\Models\Blog;

class RouteRepository {

    public static function createRoute(
        Blog $blog,
        string $name,
        string $match,
        string $template,
        string $postsFilter = null,
        string $contentType = null,
    ) {
        
        $blog->routes()->create([
            'name' => $name,
            'match' => $match,
            'template' => $template,
            'posts_filter' => $postsFilter,
            'content_type' => $contentType
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