<?php

use App\Http\Controllers\DataAPI\DataAPIController;
use App\Http\Middleware\App\DataAPIMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/data/v0/blog/{subdomain}')
    ->middleware(SubdomainMiddleware::class)
    ->group(function() {

    Route::get('/post', [DataAPIController::class, 'post']);
    Route::get('/tag', [DataAPIController::class, 'tag']);
    Route::get('/author', [DataAPIController::class, 'author']);
    Route::get('/blog', [DataAPIController::class, 'blog']);

    Route::get('/posts', [DataAPIController::class, 'posts']);
    Route::get('/tags', [DataAPIController::class, 'tags']);
    Route::get('/authors', [DataAPIController::class, 'authors']);

});
