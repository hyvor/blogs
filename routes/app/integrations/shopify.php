<?php

use App\Http\Controllers\Integrations\Shopify\ShopifyController;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::prefix('shopify')
    ->middleware(StartSession::class)
    ->group(function () {
        Route::get('/', [ShopifyController::class, 'init']);
        Route::get('/installed', [ShopifyController::class, 'installed'])->name('shopify-installed');
        Route::get('/complete', [ShopifyController::class, 'complete'])->name('shopify-complete');

        Route::get('/proxy/{path?}', [ShopifyController::class, 'proxy'])->where('path', '.*');

        Route::get('/billing/confirm', [ShopifyController::class, 'confirmSubscription'])->name('shopify-billing-create');

        Route::prefix('webhook')->group(function() {

            /**
             * We do not collect customer data
             * So, just reply with 200 OK
             */
            Route::post('customer-data-request', fn () => 'ok');
            Route::post('customer-data-erasure', fn () => 'ok');
            Route::post('shop-data-erasure', [ShopifyController::class, 'deleteShop']);

        });
    });
