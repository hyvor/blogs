<?php

use App\Http\InternalApi\SudoController;
use Hyvor\Internal\InternalApi\Middleware\InternalApiFromMiddleware;
use Hyvor\Internal\InternalApi\Middleware\InternalApiMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/internal')
    ->middleware(InternalApiMiddleware::class)
    ->group(function () {
        Route::prefix('/core')
            ->middleware(InternalApiFromMiddleware::class . ':core')
            ->group(function () {
                Route::prefix('/sudo')->group(function () {
                    Route::post('/overview', [SudoController::class, 'overview']);
                    Route::post('/blogs', [SudoController::class, 'getBlogs']);
                    Route::post('/blogs/{id}', [SudoController::class, 'blogAction']);
                });
            });
    });
