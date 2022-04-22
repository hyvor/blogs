<?php
namespace App\Domains\Route;

use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Route;
use Illuminate\Database\Eloquent\Collection;
class RouteRepository {
    
    const DEFAULT_ROUTES = [
        // post
        [
            'name' => 'post',
            'match' => '/{slug}',
            'template' => 'post'
        ],
        // page
        [
            'name' => 'page',
            'match' => '/{slug}',
            'template' => 'page,post'
        ],
        // home page (index)
        [
            'name' => 'index',
            'match' => '/',
            'template' => 'index',
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
            'match' => '/search/{search}',
            'template' => 'search,index'
        ]
    ];



    public static function getRoute(Blog $blog, string $name) {

        /**
        * Even calling ->routes fetches all routes
        * it fetches the relationship, so it prevents calling more duplicate queries from 
        * the Blog model's route relationship
        */
        $routes = $blog->routes;
        return $routes->where('name', $name)->first();
    }

    public static function getRoutes(Blog $blog) : Collection {

        return Route::where('blog_id','=', $blog->id)
            ->latest()
            ->get(); 
    }

    public static function createRoute(
        Blog $blog,
        string $name,
        string $match,
        string $template,
        string $postsFilter = null,
        string $contentType = null, 
    ) : void 
    {
        $blog->routes()->create([
            'name' => $name,
            'match' => $match,
            'template' => $template,
            'posts_filter' => $postsFilter,
            'content_type' => $contentType
        ]);
    }

    public static function updateRoute(
        int $id, 
        string $name, 
        string $match, 
        string $template, 
        string $postsFilter = null, 
        string $contentType = null,
    ) : bool 
    {
        return Route::find($id)
            ->update([
                'name' => $name,
                'match' => $match,
                'template' => $template,
                'posts_filter' => $postsFilter ?? null,
                'content_type' => $contentType ?? null,
            ]);
    }

    public static function deleteRoute(int $id) : bool {
        $data = Route::find($id);
        return $data->delete();
    }
}

