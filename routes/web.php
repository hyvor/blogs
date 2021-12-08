<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\ImportExportController;


// Route::get('/', function () {
//     return view('posts.index');
//     // return View::make('layouts/app');
// });

Route::get('/', function () {
    return view('test.test');
});

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