<?php


use App\Http\PublicApi\BillingController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/public')->group(function () {

    Route::get('/billing/success', [BillingController::class, 'success']);

});