<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\App\SubdomainMiddleware;
use App\Http\Controllers\DataAPI\DataAPIController;

/*
Route::domain('{subdomain}.' . config('app.domain_delivery'))
    ->middleware(SubdomainMiddleware::class)
    ->group(function() {

        // data API
        Route::prefix('/api/data/v0')
            ->group(function() {

                Route::get('/post', [DataAPIController::class, 'post']);

            });

    });
 */

Route::domain('{subdomain}.hyvorblogs.test')->middleware('blogDeliver')->group(function () {


    Route::get('{url}', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'getUrl'])->where('url', '.+');


    // // Call to css,js,png & other files.
    // Route::get('assets/{fileName}', [App\Http\Controllers\Delivery\AssetController::class, 'assets']);
    // // Language change
    // Route::get('language', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'languageChange']);


    // // Testing Theme Route
    // Route::get('/test', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'test']);

    // // Main Theme Routes
    // Route::get('/', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'index'])->name('/');
    // Route::get('/author/{slug}', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'author']);
    // Route::get('/tag/{tagName}', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'tag']);
    // Route::get('/{name}', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'pages'])->where('any', '.*');



    // // Select a specific theme for the blog
    // Route::get('/theme', [App\Http\Controllers\ThemesController::class, 'selectTheme'])->name('/theme');

});