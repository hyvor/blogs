<?php

use App\Http\Controllers\Special\CaddyController;
use App\Http\Controllers\Special\GithubThemePingController;
use Illuminate\Support\Facades\Route;

Route::prefix('/special')->group(function() {

    Route::get('caddy/allowed-domain', [CaddyController::class, 'checkDomain']);
    Route::get('themes/ping', [GithubThemePingController::class, 'ping']);

});