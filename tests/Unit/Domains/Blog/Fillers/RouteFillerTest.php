<?php

namespace Tests\Unit\Domains\Blog\Fillers;

use App\Domains\Blog\Fillers\RouteFiller;
use App\Models\Route;

it('fills default routes', function() {

    $blog = newBlog();

    (new RouteFiller($blog))->fill();

    $routes = $blog->routes;

    expect($routes->firstWhere('name', 'post'))->toBeInstanceOf(Route::class);
    expect($routes->firstWhere('name', 'page'))->toBeInstanceOf(Route::class);
    expect($routes->firstWhere('name', 'index'))->toBeInstanceOf(Route::class);
    expect($routes->firstWhere('name', 'tag'))->toBeInstanceOf(Route::class);
    expect($routes->firstWhere('name', 'author'))->toBeInstanceOf(Route::class);

});