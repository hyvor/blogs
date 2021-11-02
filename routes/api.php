<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::domain('blogs.hyvor.test')->group(function () {
    Route::get('blogs', [App\Http\Controllers\API\BlogController::class, 'index']);
});


// Delivery API 
Route::domain('{account}.hyvorblogs.test')->group(function () {
    Route::get(
        '/',function ($account) {
            
            $themeCtr = new ThemeBuilderController(); 
            return $themeCtr->index();
    });
});