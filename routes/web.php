<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\PostController;

// Route::get('/', function () {
//     // return view('posts.index');
//     return View::make('layouts/app');
// });

 
Route::domain('{account}.hyvorblogs.test')->group(function () {
    Route::get(
        '/',function ($account) {
            // $blogCtr = new BlogController(); 
            // $entity = $blogCtr->loadBlog($account); 
            // return $entity['payload'];
            return "hello world";
    });
    Route::get(
        '/{slug}',function ($account,$slug) {
            $postCtr = new PostController();
            $entity = $postCtr->loadPost($account,$slug);
            return $entity['payload'];
    });

});