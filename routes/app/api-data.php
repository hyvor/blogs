<?php

use App\Http\Controllers\DataAPI\DataAPIController;
use App\Http\Controllers\DataAPI\DataAPIPostsController;
use App\Http\Controllers\DataAPI\DataAPITagsController;
use App\Http\Middleware\App\DataAPIMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/data/v0/{subdomain}')
    ->middleware(SubdomainMiddleware::class)
    ->group(function() {

    Route::get('/post', [DataAPIPostsController::class, 'post']);
    Route::get('/posts', [DataAPIPostsController::class, 'posts']);
    Route::get('/posts/search', [DataAPIPostsController::class, 'postsSearch']);

    Route::get('/tag', [DataAPITagsController::class, 'tag']);
    Route::get('/tags', [DataAPITagsController::class, 'tags']);

    Route::get('/author', [DataAPIController::class, 'author']);
    Route::get('/authors', [DataAPIController::class, 'authors']);

    Route::get('/blog', [DataAPIController::class, 'blog']);

});
