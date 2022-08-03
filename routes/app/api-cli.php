<?php

/**
 * API for the CLI (Theme Development)
 */

use App\Http\Controllers\CliAPI\CliAPIController;
use App\Http\Middleware\App\CliAPI\CliAPIMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/cli/{subdomain}')
    ->middleware(CliAPIMiddleware::class)
    ->group(function () {
        Route::patch('/files', [CliAPIController::class, 'updateFiles']);
        // Route::get('/delivery', [CliAPIController::class, 'delivery']);
    });
