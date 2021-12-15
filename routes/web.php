<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\PostController;

// Route::get('/', function () {
//     // return view('posts.index');
//     return View::make('layouts/app');
// });

// Auth::routes();

// Route::domain(config('app.domain_app'))->group(function() {
    
//     Route::view('/{any?}', 'console')->where('any', '.*');

// });


/* Route::get('/', [App\Http\Controllers\PostController::class, 'index'])->name('index');
Route::get('/home', [App\Http\Controllers\PostController::class, 'index'])->name('home');
Route::get('/post/create', [App\Http\Controllers\PostController::class, 'create']);
Route::post('/post', [App\Http\Controllers\PostController::class, 'store']);
Route::get('/post/{post}/edit', [App\Http\Controllers\PostController::class, 'edit']);
Route::get('/post/{post}', [App\Http\Controllers\PostController::class, 'show']);
Route::put('/post/{post}', [App\Http\Controllers\PostController::class, 'update']);
Route::delete('/post/{post}', [App\Http\Controllers\PostController::class, 'destroy']);
 */


// Delevery Routes
Route::domain('{account}.hyvorblogs.test')->group(function () {

    // Pages in the blog
    Route::get('/', [App\Http\Controllers\Delevery\ThemeDeleveryController::class, 'index'])->name('/');
    Route::get('/author/{slug}', [App\Http\Controllers\Delevery\ThemeDeleveryController::class, 'author']);
    Route::get('/tag/{tag:name}', [App\Http\Controllers\Delevery\ThemeDeleveryController::class, 'tag']);
    Route::get('/{name}', [App\Http\Controllers\Delevery\ThemeDeleveryController::class, 'pages']);

    // Route::get('/asset/{assets}', [App\Http\Controllers\Delevery\ThemeDeleveryController::class, 'pages']);

    // Select a specific theme for the blog
    Route::get('/theme', [App\Http\Controllers\ThemesController::class, 'selectTheme'])->name('/theme');

});