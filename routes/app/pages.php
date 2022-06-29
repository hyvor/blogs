<?php

use App\Http\Controllers\Pages\DocsController;
use App\Http\Controllers\Pages\ThemesController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\App\LoginRequiredElseRedirectMiddleware;
use App\Http\Controllers\ConsoleAPI\ConsoleViewController;

// console
Route::middleware(LoginRequiredElseRedirectMiddleware::class)
    ->get('/console/{any?}', ConsoleViewController::class)
    ->where('any', '.*');

// landing
Route::view('/', 'landing.index');
Route::view('/pricing', 'landing.pricing');
Route::get('/docs/{page?}', [DocsController::class, 'handle']);
Route::get('/themes/{name?}', [ThemesController::class, 'handle']);