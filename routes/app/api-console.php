<?php

use App\Http\Controllers\ConsoleAPI\ConsoleBlogController;
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

    // posts (and pages) CRUD
    Route::get('/posts', [ConsolePostController::class, 'getPosts']);
    Route::post('/post', [ConsolePostController::class, 'createPost']);
    Route::get('/post/{id}', [ConsolePostController::class, 'getPost']);
    Route::patch('/post/{id}', [ConsolePostController::class, 'updatePost']);
    Route::delete('/post/{id}', [ConsolePostController::class, 'deletePost']);

    // users CRUD
    Route::get('/users', [ConsoleUserController::class, 'getUsers']);
    Route::post('/user', [ConsoleUserController::class, 'createUser']);
    Route::patch('/user/{id}', [ConsoleUserController::class, 'updateUser']);
    Route::delete('/user/{id}', [ConsoleUserController::class, 'deleteUser']);

    // tags CRUD
    Route::get('/tags', []);
    Route::post('/tag', []);
    Route::patch('/tag/{id}', []);
    Route::delete('/tag/{id}', []);

    // theme CRUD
    Route::get('/theme/files', []);
    Route::post('/theme/file/{name}', []);
    Route::post('/theme/{themeId}', []);
    Route::post('/theme/upload', []);

    // webhooks CRUD
    Route::get('/webhooks', []);
    Route::post('/webhook', []);
    Route::patch('/webhook/{id}', []);
    Route::delete('/webhook/{id}', []);

    // navigation CRUD
    Route::get('/navigations', []);
    Route::post('/navigation', []);
    Route::patch('/navigation/{id}', []);
    Route::delete('/navigation/{id}', []);

    // billing CRUD
    Route::get('/subscription', []);
    Route::post('/subscription', []);
    Route::patch('/subscription', []);
    Route::delete('/subscription', []);

    // settings RU
    Route::get('/settings', []);
    Route::post('/settings', []);

    // misc
    Route::get('/counts', [ConsoleBlogController::class, 'getPostsCounts']);

    // platform-specific
    Route::get('/themes', []);

});