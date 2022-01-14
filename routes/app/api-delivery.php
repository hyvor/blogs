<?php

use App\Http\Controllers\DeliveryAPI\DeliveryAPIController;
use Illuminate\Support\Facades\Route;


// Route::get('/api/delivery/v0/blog/{subdomain}', [DeliveryAPIController::class, 'get'])->where('subdomain', '.*');

// Route::prefix('/api/delivery/v0/blog')->group(function() {
//     Route::get('{subdomain}', [DeliveryAPIController::class, 'get'])->where('subdomain', '.*');
// });

// Route::domain('hyvorblogs.test')->group(function () {

    Route::get('/api/delivery/v0/blog/{subdomain}', [DeliveryAPIController::class, 'get'])->where('subdomain', '.*');

// }); 