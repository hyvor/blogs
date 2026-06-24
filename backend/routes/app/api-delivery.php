<?php

use App\Http\Controllers\DeliveryAPI\DomainDeliveryController;
use App\Http\Middleware\App\Delivery\CustomDomainMiddleware;
use App\Http\Middleware\App\Delivery\RedirectIfNotOnSubdomainMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

$deliveryDomain = preg_replace('/https?:\/\//', '', config('blogs.delivery_url'));

// subdomain
Route::domain('{subdomain}.' . $deliveryDomain)
    ->middleware([
        SubdomainMiddleware::class,
        RedirectIfNotOnSubdomainMiddleware::class,
    ])
    ->get('{path}', [DomainDeliveryController::class, 'handle'])->where('path', '.*');

// custom domain
Route::middleware(CustomDomainMiddleware::class)
    ->domain('{domain}')
    ->get('{any}', [DomainDeliveryController::class, 'handle'])
    ->where(
        'domain',
        '^(?!' .
        str_replace('.', '\.', strval(config('blogs.domain_app'))) .
        ').*$'
    )
    ->where('any', '.*');
