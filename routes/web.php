<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\ImportExportController;


// Route::get('/', function () {
//     return view('posts.index');
//     // return View::make('layouts/app');
// });

// Auth::routes();

Route::domain(config('app.domain_app'))->group(function() {
    
    // landing pages
    Route::view('/', 'landing.index');
    Route::view('/pricing', 'landing.pricing');

    Route::view('/console/{any?}', 'console')->where('any', '.*');

});

/* Route::get('/', [App\Http\Controllers\PostController::class, 'index'])->name('index');
Route::get('/home', [App\Http\Controllers\PostController::class, 'index'])->name('home');
Route::get('/post/create', [App\Http\Controllers\PostController::class, 'create']);
Route::post('/post', [App\Http\Controllers\PostController::class, 'store']);
Route::get('/post/{post}/edit', [App\Http\Controllers\PostController::class, 'edit']);
Route::get('/post/{post}', [App\Http\Controllers\PostController::class, 'show']);
Route::put('/post/{post}', [App\Http\Controllers\PostController::class, 'update']);
Route::delete('/post/{post}', [App\Http\Controllers\PostController::class, 'destroy']);
 */

Route::domain('{account}.hyvorblogs.test')->group(function () {
    Route::get(
        '/',function ($account) {
            $blogCtr = new BlogController(); 
            $entity = $blogCtr->loadBlog($account); 
            return $entity['payload'];
    });
    Route::get(
        '/{slug}',function ($account,$slug) {
            $postCtr = new PostController();
            $entity = $postCtr->loadPost($account,$slug);
            return $entity['payload'];
    });

});

//Import and export from other CDNs
Route::get('/', [ImportExportController::class, 'index']);