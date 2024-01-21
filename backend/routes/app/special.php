<?php declare(strict_types=1);

use App\Http\ConsoleApi\Controllers\ConsoleController;
use App\Http\Controllers\ConsoleAPI\ConsoleThemeController;
use App\Http\Controllers\Special\CaddyController;
use App\Http\Controllers\Special\GithubThemeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/special')->group(function () {

    Route::get('caddy/allowed-domain', [CaddyController::class, 'checkDomain']);
    Route::post('themes/publish', [GithubThemeController::class, 'publish']);

    Route::get('/error', function() {
        throw new Exception('This is a test exception');
    });

    Route::get('/themes', [ConsoleThemeController::class, 'getAllThemes']);
    Route::get('/config', [ConsoleController::class, 'getConfig']);
});