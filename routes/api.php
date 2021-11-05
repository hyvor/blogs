<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\PostController;

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
    Route::get('themes', [App\Http\Controllers\API\ThemeBuilderController::class, 'index']);
    Route::get('themes/{theme}', [App\Http\Controllers\API\ThemeBuilderController::class, 'show']);
    Route::post('themes', [App\Http\Controllers\API\ThemeBuilderController::class, 'store']);
    Route::put('themes/{theme}', [App\Http\Controllers\API\ThemeBuilderController::class, 'update']);
    Route::delete('themes/{theme}', [App\Http\Controllers\API\ThemeBuilderController::class, 'destroy']);

    Route::get('blogs', [App\Http\Controllers\API\BlogController::class, 'index']);
    Route::get('blogs/{blog}', [App\Http\Controllers\API\BlogController::class, 'show']);        
    Route::post('blogs', [App\Http\Controllers\API\BlogController::class, 'store']);
    Route::post('blogs/blog-name', [App\Http\Controllers\API\BlogController::class, 'checkSubdomainExist']);    
    Route::put('blogs/{blog}', [App\Http\Controllers\API\BlogController::class, 'update']);
    Route::delete('blogs/{blog}', [App\Http\Controllers\API\BlogController::class, 'destroy']);

    Route::get('post', [App\Http\Controllers\API\PostController::class, 'index']);
    Route::get('post/{post}', [App\Http\Controllers\API\PostController::class, 'show']);   
    Route::post('post', [App\Http\Controllers\API\PostController::class, 'store']);
    Route::put('post/{post}', [App\Http\Controllers\API\PostController::class, 'update']);
    Route::delete('post/{post}', [App\Http\Controllers\API\PostController::class, 'destroy']);
});


// Delivery API 
Route::domain('{account}.hyvorblogs.test')->group(function () {
    Route::get(
        '/',function ($account) {
            $blogCtr = new BlogController(); 
            $entity = $blogCtr->loadBlog($account); 
            return $entity['payload']; 

            // return response($entity, 200)
            // ->header('Content-Type', 'text/html')
            // ->header('Authorization','Bearer '.'<token>')
            // ->header('Accept','application/json');
    });
    Route::get(
        '/{slug}',function ($account,$slug) {
            $postCtr = new PostController();
            $entity = $postCtr->loadPost($account,$slug);
            return $entity['payload']; 

            // return response($entity, 200)
            // ->header('Content-Type', 'text/html')
            // ->header('Authorization','Bearer '.'<token>')
            // ->header('Accept','application/json');

    });

});