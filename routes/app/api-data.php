<?php

use App\Http\Controllers\DataAPI\DataAPIController;
use App\Http\Controllers\DataAPI\PostsController;
use App\Http\Controllers\DataAPI\TagsController;
use App\Http\Middleware\App\DataAPIMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/data/v0/{subdomain}')
    ->middleware(SubdomainMiddleware::class)
    ->group(function() {

    Route::get('/post', [PostsController::class, 'post']);
    Route::get('/posts', [PostsController::class, 'posts']);
    Route::get('/posts/search', [PostsController::class, 'postsSearch']);

    Route::get('/tag', [TagsController::class, 'tag']);
    Route::get('/tags', [TagsController::class, 'tags']);

    Route::get('/author', [DataAPIController::class, 'author']);
    Route::get('/authors', [DataAPIController::class, 'authors']);

    Route::get('/blog', [DataAPIController::class, 'blog']);

});
