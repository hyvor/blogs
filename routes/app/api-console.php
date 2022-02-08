<?php

use App\Http\Controllers\ConsoleAPI\ConsoleBlogController;
use App\Http\Controllers\ConsoleAPI\ConsoleBlogThemeController;
use App\Http\Controllers\ConsoleAPI\ConsoleEmbedController;
use App\Http\Controllers\ConsoleAPI\ConsoleMediaController;
use App\Http\Controllers\ConsoleAPI\ConsolePostController;
use App\Http\Controllers\ConsoleAPI\ConsoleSubscriptionController;
use App\Http\Controllers\ConsoleAPI\ConsoleUserController;
use App\Http\Controllers\ConsoleAPI\ConsoleViewController;
use App\Http\Controllers\ConsoleAPI\ConsoleRedirectController;

use App\Http\Middleware\App\ConsoleAPI\BlogAccessMiddleware;
use App\Http\Middleware\App\LoginRequiredMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(LoginRequiredMiddleware::class)
    ->get('/console/{any?}', ConsoleViewController::class)
    ->where('any', '.*');

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
// Important! see readme.md to see how to write these routes securely

Route::prefix('/api/console/v0/blog/{subdomain}')
    ->middleware([
        // converts {subdomain} tp Blog model
        SubdomainMiddleware::class,

        // checks blog access
        // and relationship to the blog, for resources that have {id} in route
        BlogAccessMiddleware::class,
    ])
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

    // media CRD
    Route::get('/media', [ConsoleMediaController::class, 'getFiles']);
    Route::post('/media', [ConsoleMediaController::class, 'uploadFile']);
    Route::delete('/media/{id}', [ConsoleMediaController::class, 'deleteFile']);

    // embed R
    Route::get('/embed', [ConsoleEmbedController::class, 'getData']);


    // theme CRUD
    Route::get('/theme-files', [ConsoleBlogThemeController::class, 'getAllFiles']);
    Route::put('/theme-file/{id}', [ConsoleBlogThemeController::class, 'createOrUpdateFile']);

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
    Route::get('/subscription', [ConsoleSubscriptionController::class, 'getData']);
    Route::post('/subscription', [ConsoleSubscriptionController::class, 'createPayLink']);
    Route::patch('/subscription', [ConsoleSubscriptionController::class, 'updateSubscription']);
    Route::delete('/subscription', [ConsoleSubscriptionController::class, 'cancelSubscription']);

    // redirects CRUD
    Route::get('/redirect', [ConsoleRedirectController::class, 'getRedirects']);
    Route::post('/redirect', [ConsoleRedirectController::class, 'createRedirect']);
    Route::put('/redirect/{id}', [ConsoleRedirectController::class, 'updateRedirect']);
    Route::delete('/redirect/{id}', [ConsoleRedirectController::class, 'deleteRedirect']);

    // settings RU
    Route::get('/settings', []);
    Route::post('/settings', []);

    // misc
    Route::get('/counts', [ConsoleBlogController::class, 'getPostsCounts']);

    // platform-specific
    Route::get('/themes', []);

});