<?php

namespace Tests\Unit\Domains\Blog\Fillers;

use App\Domains\Blog\Fillers\RouteFiller;
use App\Models\Route;

it('fills default routes', function () {
    $blog = newBlog();

    (new RouteFiller($blog))->fill();

    $routes = $blog->routes;

    $post = $routes->firstWhere('name', 'post');
    expect($post)->toBeInstanceOf(Route::class);
    expect($post->posts_filter)->toBeNull();
    expect($routes->firstWhere('name', 'page'))->toBeInstanceOf(Route::class);

    $index = $routes->firstWhere('name', 'index');
    expect($index)->toBeInstanceOf(Route::class);
    expect($index->posts_filter)->not->toBeNull();
    expect($routes->firstWhere('name', 'tag'))->toBeInstanceOf(Route::class);
    expect($routes->firstWhere('name', 'author'))->toBeInstanceOf(Route::class);
});
