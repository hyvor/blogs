<?php

use App\Http\Controllers\ConsoleAPI\ConsoleBlogController;
use App\Http\Controllers\ConsoleAPI\ConsoleBlogThemeController;
use App\Http\Controllers\ConsoleAPI\ConsoleEmbedController;
use App\Http\Controllers\ConsoleAPI\ConsoleLanguageController;
use App\Http\Controllers\ConsoleAPI\ConsoleMediaController;
use App\Http\Controllers\ConsoleAPI\ConsolePostController;
use App\Http\Controllers\ConsoleAPI\ConsoleSubscriptionController;
use App\Http\Controllers\ConsoleAPI\ConsoleUserController;
use App\Http\Controllers\ConsoleAPI\ConsoleViewController;
use App\Http\Controllers\ConsoleAPI\ConsoleRedirectController;
use App\Http\Controllers\ConsoleAPI\ConsoleNavigationController;
use App\Http\Controllers\ConsoleAPI\ConsoleTagController;


// use App\Http\Middleware\App\ConsoleAPI\BlogAccessMiddleware;
// use App\Http\Middleware\App\LoginRequiredMiddleware;

use App\Http\Middleware\App\ConsoleAPI\ConsoleApiAccessMiddleware;
use App\Http\Middleware\App\ConsoleAPI\PostAuthorshipMiddleware;
use App\Http\Middleware\App\ConsoleAPI\ResourceAccessMiddleware;
use App\Http\Middleware\App\LoginRequiredElseRedirectMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

// Route::middleware(LoginRequiredElseRedirectMiddleware::class)
//     ->get('/console/{any?}', ConsoleViewController::class)
//     ->where('any', '.*');

Route::get('/console/{any?}', ConsoleViewController::class)
    ->where('any', '.*');


// this is an internal API
Route::prefix('/api/console')
    // add middleware
    ->group(function() {

    Route::post('/user/blog', [ConsoleUserController::class, 'createBlog']);
    Route::patch('/user/blogs/sort', [ConsoleUserController::class, 'changeSort']);

    Route::get('/themes', []);

});

// this is the Console API
// can be used by both us and others
// Important! see BlogAccessMiddleware to see how to write these routes securely

Route::prefix('/api/console/v0/blog/{subdomain}')
    ->middleware([
        // converts {subdomain} tp Blog model
        SubdomainMiddleware::class,

        // check if the user or API key has access to the console API
        // and set App\Models\User app instance
        ConsoleApiAccessMiddleware::class,

        // checks relationship to the blog, for resources that have {id} in route
        ResourceAccessMiddleware::class,
    ])
    ->group(function() {
    
    /**
     * Posts and media
     * @access ALL except Finance
     */
    Route::middleware('role:owner|admin|editor|writer|contributor')->group(function() {

        // blog
        Route::get('/blog', [ConsoleBlogController::class, 'getBlog']);
        Route::get('/blog/post-counts', [ConsoleBlogController::class, 'getPostsCounts']);
        
        /**
         * In post routes, role is checked internally on some actions
         * such as publishing posts
         * or editing posts or others
         */
        // posts (and pages) CRUD
        Route::get('/posts', [ConsolePostController::class, 'getPosts']);
        Route::get('/pages', [ConsolePostController::class, 'getPages']);
        Route::post('/post', [ConsolePostController::class, 'createPost']);

        // check author
        Route::middleware(PostAuthorshipMiddleware::class)->group(function() {
            Route::get('/post/{id}', [ConsolePostController::class, 'getPost']);
            Route::patch('/post/{id}', [ConsolePostController::class, 'updatePost']);
            Route::delete('/post/{id}', [ConsolePostController::class, 'deletePost']);
        });

        // media CRD
        Route::get('/media', [ConsoleMediaController::class, 'getFiles']);
        Route::post('/media', [ConsoleMediaController::class, 'uploadFile']);
        Route::delete('/media/{id}', [ConsoleMediaController::class, 'deleteFile']);

        // embed R
        Route::get('/embed', [ConsoleEmbedController::class, 'getData']);

    });

    Route::middleware('role:owner|admin|editor')->group(function() {

        // tags CRUD
        Route::get('/tags', [ConsoleTagController::class, 'getTag']);
        Route::post('/tags', [ConsoleTagController::class, 'createTag']);
        Route::put('/tag/{tagId}', [ConsoleTagController::class, 'updateTag']);
        Route::delete('/tag/{tagId}', [ConsoleTagController::class, 'deleteTag']);

        Route::get('/postTags/{postId}', [ConsoleTagController::class, 'selectedPostTag']);

        // post_tag CRUD
        // Route::get('/getTagList', [ConsoleTagController::class, 'getTagList']);
        // Route::post('/createPostTag', [ConsoleTagController::class, 'createPostTag']);
        // Route::get('/getPostTag', [ConsoleTagController::class, 'getPostTag']);
        // Route::delete('/removePostTag', [ConsoleTagController::class, 'removePostTag']);


        // comments
        Route::get('/comments/moderate', []);

    });


    /**
     * Settings, users, and theme
     * @access OWNER|ADMIN
     */
    Route::middleware('role:owner|admin')->group(function() {

        // webhooks CRUD
        Route::get('/webhooks', []);
        Route::post('/webhook', []);
        Route::patch('/webhook/{id}', []);
        Route::delete('/webhook/{id}', []);

        // navigation CRUD
        Route::get('/navigation', [ConsoleNavigationController::class,'getNavigations']);
        Route::post('/navigation', [ConsoleNavigationController::class,'createNavigation']);
        Route::put('/navigation/{id}', [ConsoleNavigationController::class,'updateNavigation']);
        Route::delete('/navigation/{id}', [ConsoleNavigationController::class,'deleteNavigation']);

        // order Navigation
        Route::put('/navNumber/{userId}', [ConsoleNavigationController::class,'updateItemNumber']);
        Route::put('/source/{sourceId}', [ConsoleNavigationController::class,'updateSourceItemNumber']);


        // languages CRUD
        Route::get('/languages', [ConsoleLanguageController::class, 'get']);
        Route::post('/language', [ConsoleLanguageController::class, 'create']);
        Route::put('/language/{id}', [ConsoleLanguageController::class, 'update']);
        Route::delete('/language/{id}', [ConsoleLanguageController::class, 'delete']);
        
        // redirects CRUD
        Route::get('/redirect', [ConsoleRedirectController::class, 'getRedirects']);
        Route::post('/redirect', [ConsoleRedirectController::class, 'createRedirect']);
        Route::put('/redirect/{id}', [ConsoleRedirectController::class, 'updateRedirect']);
        Route::delete('/redirect/{id}', [ConsoleRedirectController::class, 'deleteRedirect']);

        // settings RU
        Route::get('/settings', []);
        Route::post('/settings', []);

        // users CRUD
        Route::get('/users', [ConsoleUserController::class, 'getAuthor']);
        Route::post('/user', [ConsoleUserController::class, 'createAuthor']);
        Route::patch('/user/{id}', [ConsoleUserController::class, 'updateAuthor']); 
        Route::delete('/user/{id}', [ConsoleUserController::class, 'deleteAuthor']); 

        // theme CRUD
        Route::get('/theme-files', [ConsoleBlogThemeController::class, 'getAllFiles']);
        Route::put('/theme-file/{id}', [ConsoleBlogThemeController::class, 'createOrUpdateFile']);

    });

    /**
     * Billing
     * @access OWNER|ADMIN|FINANCE
     */
    Route::middleware('role:owner|admin|finance')->group(function() {

        // billing CRUD
        Route::get('/subscription', [ConsoleSubscriptionController::class, 'getData']);
        Route::post('/subscription', [ConsoleSubscriptionController::class, 'createPayLink']);
        Route::patch('/subscription', [ConsoleSubscriptionController::class, 'updateSubscription']);
        Route::delete('/subscription', [ConsoleSubscriptionController::class, 'cancelSubscription']);

    });

});