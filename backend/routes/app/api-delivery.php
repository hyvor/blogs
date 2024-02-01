<?php

use App\Http\Controllers\DeliveryAPI\DeliveryAPIController;
use App\Http\Controllers\DeliveryAPI\DomainDeliveryController;
use App\Http\Middleware\App\Delivery\CustomDomainMiddleware;
use App\Http\Middleware\App\Delivery\DeliveryApiKeyMiddleware;
use App\Http\Middleware\App\Delivery\RedirectIfNotOnSubdomainMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

// direct API
Route::domain(config('blogs.domain_app'))
    ->middleware([SubdomainMiddleware::class, DeliveryApiKeyMiddleware::class])
    ->get('/api/delivery/v0/{subdomain}', [DeliveryAPIController::class, 'handle']);

// subdomain
Route::domain('{subdomain}.'.config('blogs.domain_delivery'))
    ->middleware([
        SubdomainMiddleware::class,
        RedirectIfNotOnSubdomainMiddleware::class,
    ])
    ->get('{path}', [DomainDeliveryController::class, 'handle'])->where('path', '.*');

// custom domain
Route::middleware(CustomDomainMiddleware::class)
    ->domain('{domain}')
    ->get('{any}', [DomainDeliveryController::class, 'handle'])
    ->where('domain',
        '^(?!' .
        str_replace('.', '\.', strval(config('blogs.domain_app'))) .
        ').*$'
    )
    ->where('any', '.*');
