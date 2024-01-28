<?php declare(strict_types=1);

use App\Domains\Delivery\Twig\DataAPICaller;
use App\Http\Controllers\DataApi\AuthorsController;
use App\Http\Controllers\DataApi\BlogController;
use App\Http\Controllers\DataApi\PostsController;
use App\Http\Controllers\DataApi\TagsController;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Request as Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/data/v0/{subdomain}')
    ->middleware(SubdomainMiddleware::class)
    /*->get('{any}', function() {

        $path = Request::path();
        $input = Request::input();
        $caller = new DataAPICaller($path, $input);


    });*/
    ->group(function () {
        Route::get('/post', [PostsController::class, 'post']);
        Route::get('/posts', [PostsController::class, 'posts']);
        Route::get('/posts/search', [PostsController::class, 'postsSearch']);

        Route::get('/tag', [TagsController::class, 'tag']);
        Route::get('/tags', [TagsController::class, 'tags']);

        Route::get('/author', [AuthorsController::class, 'author']);
        Route::get('/authors', [AuthorsController::class, 'authors']);

        Route::get('/blog', [BlogController::class, 'blog']);
    });
