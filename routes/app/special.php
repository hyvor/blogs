<?php

use App\Http\Controllers\Special\CaddyController;
use Illuminate\Support\Facades\Route;

Route::prefix('/special')
    ->group(function() {

    Route::get('caddy/allowed-domain', [CaddyController::class, 'checkDomain']);

});
