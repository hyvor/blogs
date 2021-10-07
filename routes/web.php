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

Route::get('/theme/create', [App\Http\Controllers\ThemeBuilderController::class, 'createThemeForm']);
Route::get('/theme/list', [App\Http\Controllers\ThemeBuilderController::class, 'themesListAll']);
Route::post('/theme/submit-theme', [App\Http\Controllers\ThemeBuilderController::class, 'saveTheme']);
Route::get('/theme/{id}', [App\Http\Controllers\ThemeBuilderController::class, 'loadThemeMarkup']);
Route::delete('/theme/{theme}', [App\Http\Controllers\ThemeBuilderController::class, 'destroy']);

Route::get('/blog/create', [App\Http\Controllers\ThemeBuilderController::class, 'createThemeForm']);
Route::get('/blog/list', [App\Http\Controllers\ThemeBuilderController::class, 'themesListAll']);
Route::post('/blog/save', [App\Http\Controllers\ThemeBuilderController::class, 'saveTheme']);
Route::get('/blog/{id}', [App\Http\Controllers\ThemeBuilderController::class, 'loadThemeMarkup']);
Route::delete('/blog/{blog}', [App\Http\Controllers\ThemeBuilderController::class, 'destroy']);