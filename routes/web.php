<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\PostController;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

// Route::get('/', function () {
//     // return view('posts.index');
//     return View::make('layouts/app');
// });

// Auth::routes();

include('app/subdomain.php');


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


// return function (RoutingConfigurator $routes) {
//         $routes->add('scripts', '/')
//         ->controller([App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'testScripts']);
// };


// Delevery Routes
Route::domain('{subdomain}.hyvorblogs.test')->middleware('blogDeliver')->group(function () {

    Route::get('assets/{assetFile}', [App\Http\Controllers\Delivery\AssetController::class, 'assets']);

    Route::get('/test', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'test']);

    // Assets
    Route::get('language', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'languageChange']);


    // Pages in the blog
    Route::get('/', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'index'])->name('/');
    Route::get('/author/{slug}', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'author']);
    Route::get('/tag/{tag:name}', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'tag']);
    Route::get('/{name}', [App\Http\Controllers\Delivery\ThemeDeleveryController::class, 'pages'])->where('any', '.*');

    // Route::get('/asset/{assets}', [App\Http\Controllers\Delevery\ThemeDeleveryController::class, 'pages']);

    // Select a specific theme for the blog
    Route::get('/theme', [App\Http\Controllers\ThemesController::class, 'selectTheme'])->name('/theme');

    include('app/pages.php');

    include('app/api-data.php');
    include('app/api-delivery.php');
    include('app/api-console.php');


});