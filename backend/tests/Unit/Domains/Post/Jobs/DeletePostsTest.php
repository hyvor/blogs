<?php

namespace Tests\Unit\Domains\Post\Jobs;

use App\Domains\Post\Jobs\DeletePosts;

it('it deletes posts of the blog', function () {

    $blog1 = blogWithLanguage();
    $blog2 = blogWithLanguage();

    addPosts($blog1, 5);
    addPosts($blog2, 4);

    DeletePosts::dispatch($blog1);

    expect($blog1->posts()->count())->toBe(0);
    expect($blog2->posts()->count())->toBe(4);
});
