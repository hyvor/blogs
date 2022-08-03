<?php

use App\Http\Controllers\DeliveryAPI\DeliveryAPIController;
use App\Http\Middleware\App\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/api/delivery/v0/{subdomain}', [DeliveryAPIController::class, 'handle'])
    ->middleware(SubdomainMiddleware::class);
