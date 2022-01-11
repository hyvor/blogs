<?php

use App\Http\Controllers\DeliveryAPI\DeliveryAPIController;
use Illuminate\Support\Facades\Route;


Route::get('/api/delivery/v0/blog/{subdomain}', [DeliveryAPIController::class, 'get']);