<?php

use App\Http\Controllers\ConsoleAPI\ConsoleViewController;
use App\Http\Controllers\Pages\DocsController;
use App\Http\Controllers\Pages\ThemesController;
use App\Http\Middleware\App\LoginRequiredElseRedirectMiddleware;
use Illuminate\Support\Facades\Route;

// console
Route::middleware(LoginRequiredElseRedirectMiddleware::class)
    ->get('/console/{any?}', ConsoleViewController::class)
    ->where('any', '.*');

// landing
Route::view('/', 'landing.index');
Route::view('/pricing', 'landing.pricing');
Route::get('/docs/{page?}', [DocsController::class, 'handle']);
Route::get('/themes/{name?}', [ThemesController::class, 'handle']);
Route::view('/config', 'config');
