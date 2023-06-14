<?php

namespace Tests\Unit\Domains\Blog\Jobs;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Jobs\UpdateContentHtmlOfAllPostsJob;
use App\Domains\Post\Content\PostContentService;
use App\Models\PostVariant;
use Mockery\MockInterface;

it('updates post variants', function() {

    $count = 0;

    PostVariant::updated(function($variant) use (&$count) {
        $count++;
    });

    $blog = blog();
    (new LanguageFiller($blog))->fill();

    addPosts($blog, 3, [], ['status' => 'published']);

    $job = new UpdateContentHtmlOfAllPostsJob($blog);
    $job->handle();

    expect($count)->toBe(3);

});