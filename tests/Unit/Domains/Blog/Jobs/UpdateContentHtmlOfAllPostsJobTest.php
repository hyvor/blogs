<?php

namespace Tests\Unit\Domains\Blog\Jobs;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Jobs\UpdateContentHtmlOfAllPostsJob;
use App\Domains\Post\Content\PostContentService;
use Mockery\MockInterface;

it('updates post variants', function() {

    $this->mock(PostContentService::class, function (MockInterface $mockery) {
        $mockery->shouldReceive('updateVariantHtml')->times(3);
    });

    $blog = blog();
    (new LanguageFiller($blog))->fill();

    addPosts($blog, 3, [], ['status' => 'published']);

    $job = new UpdateContentHtmlOfAllPostsJob($blog);
    $job->handle();

});