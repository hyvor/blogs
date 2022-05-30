<?php

use App\Http\Controllers\Special\CaddyController;
use App\Http\Controllers\Special\ThemeController;
use Illuminate\Support\Facades\Route;
use App\Domains\Theme\Jobs\ThemesJob;

Route::prefix('/special')
    ->group(function() {

    Route::get('caddy/allowed-domain', [CaddyController::class, 'checkDomain']);
    // Route::get('themes', [ThemeController::class, 'themes']);
    Route::get('themes', [ThemesJob::class, 'themes']);

});
