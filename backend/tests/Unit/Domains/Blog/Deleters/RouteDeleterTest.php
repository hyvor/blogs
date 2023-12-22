<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\RouteDeleter;
use App\Models\Route;

it('deletes routes', function () {
    $blog = blog();

    Route::factory()->count(3)->create(['blog_id' => $blog]);

    expect($blog->routes()->count())->toBe(3);

    (new RouteDeleter($blog))->delete();

    expect($blog->routes()->count())->toBe(0);
});
