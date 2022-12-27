<?php

// redirect blog to hyvor.com/blog

use Illuminate\Http\Request as Request;

Route::get('/blog/{path?}', function (Request $request, $path = '') {

    $path = strval($path);

    $map = [
        'laravel' => 'laravel-blog',
        'symfony' => 'symfony-blog',
    ];

    $path = $map[$path] ?? $path;

    return redirect('https://hyvor.com/blog/' . $path, 301);

})->where('path', '.*');