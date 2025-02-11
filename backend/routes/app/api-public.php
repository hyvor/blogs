<?php

use App\Http\PublicApi\BillingController;
use App\Http\PublicApi\MediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/public')->group(function () {
    Route::get('/billing/success', [BillingController::class, 'success']);
});

Route::get('/api/media/{path}', [MediaController::class, 'serve'])->where('path', '.*');