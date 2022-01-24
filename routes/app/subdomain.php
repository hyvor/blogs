<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\App\SubdomainMiddleware;
use App\Http\Controllers\DataAPI\DataAPIController;
use App\Http\Controllers\Subdomain\SubdomainController;
use App\Http\Middleware\App\Subdomain\RedirectIfSubdomainNotFoundMiddleware;

Route::domain('{subdomain}.hyvorblogs.test')
    ->middleware(SubdomainMiddleware::class)
    ->group(function () {
        Route::get('{path}', [SubdomainController::class, 'handle'])->where('path', '.*');
    });