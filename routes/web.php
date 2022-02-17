<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\App;
use App\Http\Middleware\App\SubdomainMiddleware;
use App\Http\Controllers\Subdomain\SubdomainController;
use App\Http\Middleware\App\CustomDomainMiddleware;


if (App::environment('local')) {
    include 'local.php';
}


// main app
Route::domain(config('blogs.domain_app'))->group(function() {

    include('app/pages.php');
    include('app/api-data.php');
    include('app/api-delivery.php');
    include('app/api-console.php');

});

// subdomain
Route::domain('{subdomain}.' . config('blogs.domain_delivery'))
    ->middleware(SubdomainMiddleware::class)
    ->get('{path}', [SubdomainController::class, 'handle'])->where('path', '.*');

// custom domain
Route::middleware(CustomDomainMiddleware::class)
    ->get('{path}', [SubdomainController::class, 'handle'])->where('path', '.*');