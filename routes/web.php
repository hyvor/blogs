<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     // return view('posts.index');
//     return View::make('layouts/app');
// });

// Auth::routes();

Route::get('/', [App\Http\Controllers\PostController::class, 'index'])->name('index');
Route::get('/home', [App\Http\Controllers\PostController::class, 'index'])->name('home');
Route::get('/post/create', [App\Http\Controllers\PostController::class, 'create']);
Route::post('/post', [App\Http\Controllers\PostController::class, 'store']);
Route::get('/post/all', [App\Http\Controllers\PostController::class, 'index']);
Route::get('/post/{post}/edit', [App\Http\Controllers\PostController::class, 'edit']);
Route::get('/post/{post}', [App\Http\Controllers\PostController::class, 'show']);
Route::put('/post/{post}', [App\Http\Controllers\PostController::class, 'update']);
Route::delete('/post/{post}', [App\Http\Controllers\PostController::class, 'destroy']);


// Route::get('/dashboard', 'DashboardController@index')->middleware('theme:dashboard-theme');
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index']);
Route::get('/log-in', [App\Http\Controllers\DashboardController::class, 'logIn']);
Route::get('/log-out', [App\Http\Controllers\DashboardController::class, 'logout']);
Route::get('/create-user', [App\Http\Controllers\DashboardController::class, 'createUser']);

Route::get('/theme/create', [App\Http\Controllers\ThemeBuilderController::class, 'createThemeForm']);
Route::get('/theme/list', [App\Http\Controllers\ThemeBuilderController::class, 'themesListAll']);
Route::post('/theme/submit-theme', [App\Http\Controllers\ThemeBuilderController::class, 'saveTheme']);
Route::get('/theme/{id}', [App\Http\Controllers\ThemeBuilderController::class, 'loadThemeMarkup']);
Route::delete('/theme/{theme}', [App\Http\Controllers\ThemeBuilderController::class, 'destroy']);

Route::get('/blogs/create', [App\Http\Controllers\BlogController::class, 'create']);
Route::get('/blogs', [App\Http\Controllers\BlogController::class, 'index']);
Route::post('/blogs', [App\Http\Controllers\BlogController::class, 'store']);
Route::get('/blogs/{blog}', [App\Http\Controllers\BlogController::class, 'show']);
Route::get('/blogs/{blog}/edit', [App\Http\Controllers\BlogController::class, 'edit']);
Route::put('/blogs/{blog}', [App\Http\Controllers\BlogController::class, 'update']);
Route::delete('/blogs/{blog}', [App\Http\Controllers\BlogController::class, 'destroy']);