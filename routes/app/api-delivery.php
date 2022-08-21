<?php

use App\Http\Controllers\DeliveryAPI\DeliveryAPIController;
use App\Http\Controllers\DeliveryAPI\DeliveryEmbedController;
use App\Http\Controllers\DeliveryAPI\DomainDeliveryController;
use App\Http\Middleware\App\Delivery\CustomDomainMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

// direct API
Route::domain(config('blogs.domain_app'))
    ->middleware(SubdomainMiddleware::class)
    ->get('/api/delivery/v0/{subdomain}', [DeliveryAPIController::class, 'handle']);

/**
 * === EMBED
 */
Route::domain(config('blogs.domain_app'))->prefix('/embed')->group(function() {

    // embed.js
    Route::get('/embed.js', [DeliveryEmbedController::class, 'embedJs']);

    // iframe
    Route::middleware(SubdomainMiddleware::class)
        ->get('/iframe/{subdomain}', [DeliveryEmbedController::class, 'iframe']);

});

// subdomain
Route::domain('{subdomain}.'.config('blogs.domain_delivery'))
    ->middleware(SubdomainMiddleware::class)
    ->get('{path}', [DomainDeliveryController::class, 'handle'])->where('path', '.*');

// custom domain
Route::middleware(CustomDomainMiddleware::class)
    ->get('{any}', [DomainDeliveryController::class, 'handle'])->where('any', '.*');