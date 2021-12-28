<?php

use App\Http\Controllers\ConsoleAPI\ConsolePostController;
use App\Http\Controllers\ConsoleAPI\ConsoleUserController;
use App\Http\Controllers\ConsoleAPI\ConsoleViewController;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;


Route::get('/console/{any?}', ConsoleViewController::class)->where('any', '.*');

// this is an internal API
Route::prefix('/api/console')
    // add middleware
    ->group(function() {

    Route::get('/user/blogs', [ConsoleUserController::class, 'getBlogs']);
    Route::post('/user/blog', [ConsoleUserController::class, 'createBlog']);
    Route::post('/user/blogs/sort', [ConsoleUserController::class, 'changeSort']);

});

// this is the Console API
// can be used by both us and others
Route::prefix('/api/console/v0/blog/{subdomain}')
    ->middleware(SubdomainMiddleware::class)
    ->group(function() {

    Route::get('/post-stats', [ConsolePostController::class, 'getStats']);

    // posts (and pages)
    Route::get('/posts', [ConsolePostController::class, 'getPosts']);
    Route::post('/post', [ConsolePostController::class, 'createPost']);
    Route::patch('/post/{id}', [ConsolePostController::class, 'updatePost']);
    Route::delete('/post/{id}', [ConsolePostController::class, 'getPosts']);

    // users
    Route::get('/users', []);
    Route::post('/user', []);
    Route::patch('/user/{id}', []);
    Route::delete('/user/{id}', []);

    // tags
    Route::get('/tags', []);
    Route::post('/tag', []);
    Route::patch('/tag/{id}', []);
    Route::delete('/tag/{id}', []);

    // post actions
    Route::post('/post/{id}/tags', []);
    Route::post('/post/{id}/authors', []);

    // theme
    Route::get('/theme/files', []);
    Route::post('/theme/file/{name}', []);
    Route::post('/theme/{themeId}', []);
    Route::post('/theme/upload', []);

    // webhooks
    Route::get('/webhooks', []);
    Route::post('/webhook', []);
    Route::patch('/webhook/{id}', []);
    Route::delete('/webhook/{id}', []);

    // navigation
    Route::get('/navigations', []);
    Route::post('/navigation', []);
    Route::patch('/navigation/{id}', []);
    Route::delete('/navigation/{id}', []);

    // billing
    Route::get('/subscription', []);
    Route::post('/subscription', []);
    Route::patch('/subscription', []);
    Route::delete('/subscription', []);

    // settings
    Route::get('/settings', []);
    Route::post('/settings', []);

    // platform-specific
    Route::get('/themes', []);

});