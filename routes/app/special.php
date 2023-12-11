<?php declare(strict_types=1);

use App\Http\Controllers\Special\CaddyController;
use App\Http\Controllers\Special\GithubThemeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/special')->group(function () {
    Route::get('caddy/allowed-domain', [CaddyController::class, 'checkDomain']);
    Route::post('themes/publish', [GithubThemeController::class, 'publish']);

    Route::get('/error', function() {
        throw new Exception('This is a test exception');
    });
});