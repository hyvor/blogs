<?php

namespace App\Domains\Route;

use App\Models\Blog;
use App\Models\Route;
use Illuminate\Database\Eloquent\Collection;

class RouteRepository
{
    public static function getRoute(Blog $blog, string $name)
    {

        /**
        * Even calling ->routes fetches all routes
        * it fetches the relationship, so it prevents calling more duplicate queries from
        * the Blog model's route relationship
        */
        $routes = $blog->routes;

        return $routes->where('name', $name)->first();
    }

    public static function getRoutes(Blog $blog): Collection
    {
        return Route::where('blog_id', '=', $blog->id)
            ->oldest()
            ->get();
    }

    public static function createRoute(
        Blog $blog,
        string $name,
        string $match,
        string $template,
        ?string $postsFilter = null,
        ?string $contentType = null,
    ): Route
    {
        return $blog->routes()->create([
            'name' => $name,
            'match' => $match,
            'template' => $template,
            'posts_filter' => $postsFilter,
            'content_type' => $contentType,
        ]);
    }

    public static function updateRoute(Route $route, array $updates) : Route
    {
        foreach ($updates as $key => $value) {
            $route->$key = $value;
        }
        $route->save();
        return $route;
    }

    public static function deleteRoute(Route $route)
    {
        $route->delete();
    }
}
