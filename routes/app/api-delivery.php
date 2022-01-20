<?php

use App\Http\Controllers\DeliveryAPI\DeliveryAPIController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\App\SubdomainMiddleware;

Route::get('/api/delivery/v0/blog/{subdomain}', [DeliveryAPIController::class, 'get']);
