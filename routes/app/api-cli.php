<?php

/**
 * 
 * API for the CLI (Theme Development)
 * 
 */

use App\Http\Controllers\CliAPI\CliAPIController;
use App\Http\Middleware\App\CliAPI\UUIDMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/cli')
    ->group(function() {

    Route::post('/new', [CliAPIController::class, 'createNewLocalDeveloper']);

    Route::prefix('/dev/{uuid}')
        ->middleware(UUIDMiddleware::class)
        ->group(function() {
            Route::patch('/files', [CliAPIController::class, 'updateFiles']);
            Route::get('/delivery', [CliAPIController::class, 'delivery']);
        });

});