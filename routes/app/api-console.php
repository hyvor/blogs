<?php

use App\Http\Controllers\ConsoleAPI\ConsoleBlogController;
use App\Http\Controllers\ConsoleAPI\ConsoleBlogThemeController;
use App\Http\Controllers\ConsoleAPI\ConsoleImportExportController;
use App\Http\Controllers\ConsoleAPI\ConsoleLanguageController;
use App\Http\Controllers\ConsoleAPI\ConsoleMediaController;
use App\Http\Controllers\ConsoleAPI\ConsolePostController;
use App\Http\Controllers\ConsoleAPI\ConsoleSubscriptionController;
use App\Http\Controllers\ConsoleAPI\ConsoleUserBlogController;
use App\Http\Controllers\ConsoleAPI\ConsoleUserController;
use App\Http\Controllers\ConsoleAPI\ConsoleRedirectController;
use App\Http\Controllers\ConsoleAPI\ConsoleNavigationController;
use App\Http\Controllers\ConsoleAPI\ConsoleTagController;
use App\Http\Controllers\ConsoleAPI\ConsoleRouteController;

use App\Http\Controllers\ConsoleAPI\ConsoleViewController;



// use App\Http\Middleware\App\ConsoleAPI\BlogAccessMiddleware;
// use App\Http\Middleware\App\LoginRequiredMiddleware;

use App\Http\Controllers\ConsoleAPI\ConsoleUrlDataController;
use App\Http\Controllers\ConsoleAPI\ConsoleWebhookController;

use App\Http\Middleware\App\ConsoleAPI\ConsoleApiAccessMiddleware;
use App\Http\Middleware\App\ConsoleAPI\ConsoleApiUserEndpointsAccessMiddleware;
use App\Http\Middleware\App\ConsoleAPI\PostAuthorshipMiddleware;
use App\Http\Middleware\App\ConsoleAPI\ResourceAccessMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

// Route::middleware(LoginRequiredElseRedirectMiddleware::class)
//     ->get('/console/{any?}', ConsoleViewController::class)
//     ->where('any', '.*');

Route::get('/console/{any?}', ConsoleViewController::class)
    ->where('any', '.*');


// this is an internal API
// Route::prefix('/api/console')
    // add middleware

/**
 * This is an internal API for user-level functions
 * This cannot be accessed via API keys
 * Used only in our Console
 */
Route::prefix('/api/console/v0')
    ->middleware(ConsoleApiUserEndpointsAccessMiddleware::class)
    ->group(function() {

    Route::post('/blog', [ConsoleUserBlogController::class, 'createBlog']);
    Route::patch('/blogs/sort', [ConsoleUserBlogController::class, 'changeSort']);
    Route::get('/blog/check-subdomain', [ConsoleUserBlogController::class, 'checkSubdomain']);

});

/** 
 * 
 * Console API
 * ======================
 * 
 * this is the Console API
 * can be used by both us and others
 * Important! see BlogAccessMiddleware to see how to write these routes securely
 */



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

        // Blog General CRUD 
        Route::post('/blog/variant', [ConsoleBlogController::class, 'createBlogVariant']);
        Route::patch('/blog', [ConsoleBlogController::class, 'updateBlog']);
        Route::post('/blog/feature/image', [ConsoleBlogController::class, 'updateBlogFeatureImage']);
        Route::post('/blog/icon', [ConsoleBlogController::class, 'updateBlogIcon']);

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

            Route::post('/post/{id}/variant', [ConsolePostController::class, 'createPostVariant']);
            Route::patch('/post/{id}/variant', [ConsolePostController::class, 'updatePostVariant']);
            Route::delete('/post/{id}/variant', [ConsolePostController::class, 'deletePostVariant']);
        });

        // media CRD
        Route::get('/media', [ConsoleMediaController::class, 'getFiles']);
        Route::post('/media', [ConsoleMediaController::class, 'uploadFile']);
        Route::delete('/media/{id}', [ConsoleMediaController::class, 'deleteFile']);
        Route::get('/media/unsplash/search', [ConsoleMediaController::class, 'searchUnsplash']);

        // url data
        Route::get('/url-data', [ConsoleUrlDataController::class, 'getData']);

    });

    Route::middleware('role:owner|admin|editor')->group(function() {

        // tags CRUD
        Route::get('/tags', [ConsoleTagController::class, 'getTags']);
        Route::post('/tag', [ConsoleTagController::class, 'createTag']);
        Route::put('/tag/{id}', [ConsoleTagController::class, 'updateTag']);
        Route::delete('/tag/{id}', [ConsoleTagController::class, 'deleteTag']);
        Route::post('/tag/variant', [ConsoleTagController::class, 'createTagVariant']);

        Route::get('/postTags/{id}', [ConsoleTagController::class, 'selectedPostTag']);

        // post_tag CRUD
        // Route::get('/getTagList', [ConsoleTagController::class, 'getPostTags']);
        // Route::post('/createPostTag', [ConsoleTagController::class, 'createPostTag']);
        // Route::get('/getPostTag', [ConsoleTagController::class, 'getPostTag']);
        // Route::delete('/removePostTag', [ConsoleTagController::class, 'deleteTagVariant']);


        // comments
        Route::get('/comments/moderate', []);

    });

    /**
     * Settings, users, and theme
     * @access OWNER|ADMIN
     */
    Route::middleware('role:owner|admin')->group(function() {

        // webhooks CRUD
        Route::get('/webhooks', [ConsoleWebhookController::class, 'getWebhooks']);
        Route::post('/webhook', [ConsoleWebhookController::class, 'createWebhook']);
        Route::patch('/webhook/{id}', [ConsoleWebhookController::class, 'updateWebhook']);
        Route::delete('/webhook/{id}', [ConsoleWebhookController::class, 'deleteWebhook']);

        // navigation CRUD
        Route::get('/navigation', [ConsoleNavigationController::class,'getNavigations']);
        Route::post('/navigation', [ConsoleNavigationController::class,'createNavigation']);
        Route::put('/navigation/{id}', [ConsoleNavigationController::class,'updateNavigation']);
        Route::delete('/navigation/{id}', [ConsoleNavigationController::class,'deleteNavigation']);
        Route::put('/navigation/sort/{id}', [ConsoleNavigationController::class,'updateSort']);
        Route::put('/navigation/source/{id}', [ConsoleNavigationController::class,'updateSourceSort']);

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
        Route::get('/users', [ConsoleUserController::class, 'getAuthors']);
        Route::post('/user', [ConsoleUserController::class, 'createAuthor']);
        Route::patch('/user/{id}', [ConsoleUserController::class, 'updateAuthor']); 
        Route::delete('/user/{id}', [ConsoleUserController::class, 'deleteAuthor']); 
        Route::post('/user/variant', [ConsoleUserController::class, 'createAuthorVariant']);
        Route::post('/user/picture', [ConsoleUserController::class, 'updatePicture']);

        // route CRUD
        Route::get('/route', [ConsoleRouteController::class, 'getRoutes']);
        Route::post('/route', [ConsoleRouteController::class, 'createRoute']);
        Route::put('/route/{id}', [ConsoleRouteController::class, 'updateRoute']);
        Route::delete('/route/{id}', [ConsoleRouteController::class, 'deleteRoute']);

        // theme CRUD
        Route::get('/theme-files', [ConsoleBlogThemeController::class, 'getAllFiles']);
        Route::put('/theme-file/{id}', [ConsoleBlogThemeController::class, 'createOrUpdateFile']);

        // import and export
        Route::get('/data/export', [ConsoleImportExportController::class, 'export']);
        Route::post('/data/import', [ConsoleImportExportController::class, 'import']);

        Route::get('/build', []);
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
