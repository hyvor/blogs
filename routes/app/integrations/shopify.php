<?php

use App\Http\Controllers\Integrations\Shopify\ShopifyController;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::prefix('shopify')
    ->middleware(StartSession::class)
    ->group(function() {

    Route::get('/', [ShopifyController::class, 'init']);
    Route::get('/installed', [ShopifyController::class, 'installed'])->name('shopify-installed');
    Route::get('/complete', [ShopifyController::class, 'complete'])->name('shopify-complete');

    Route::get('/charged', null);
    Route::get('/proxy/{path?}', [ShopifyController::class, 'proxy'])->where('path', '.*');

    Route::get('/billing/confirm', [ShopifyController::class, 'confirmSubscription'])
        ->name('shopify-billing-create');

});