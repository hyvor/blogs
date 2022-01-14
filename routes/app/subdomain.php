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

Route::domain('{subdomain}.hyvorblogs.test')->group(function () {

    // Testing Theme Route
    Route::get('/test', [App\Http\Controllers\DeliveryAPI\DeliveryAPIController::class, 'test']);
    // Select a specific theme for the blog
    // Route::get('/theme', [App\Http\Controllers\DeliveryAPI\DeliveryAPIController::class, 'selectTheme'])->name('/theme');

    Route::get('{url}', [App\Http\Controllers\DeliveryAPI\DeliveryAPIController::class, 'getUrl'])->where('url', '.*');

}); 