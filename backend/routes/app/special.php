<?php declare(strict_types=1);

use App\Http\ConsoleApi\Controllers\ConsoleController;
use App\Http\Controllers\ConsoleAPI\ConsoleThemeController;
use App\Http\Controllers\Special\CaddyController;
use App\Http\Controllers\Special\GithubThemeController;
use App\Http\Controllers\Special\SyntaxController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/special')->group(function () {
    Route::get('/themes', [ConsoleThemeController::class, 'getAllThemes']);
    Route::get('/config', [ConsoleController::class, 'getConfig']);
});

// TODO: Migrate these to public API