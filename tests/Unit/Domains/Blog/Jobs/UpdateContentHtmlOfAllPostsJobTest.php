<?php

namespace Tests\Unit\Domains\Blog\Jobs;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Jobs\UpdateContentHtmlOfAllPostsJob;
use App\Domains\Post\Content\PostContentRepository;
use Mockery\MockInterface;

it('updates post variants', function() {

    $this->mock(PostContentRepository::class, function (MockInterface $mockery) {
        $mockery->shouldReceive('updateVariantHtml')->times(3);
    });

    $blog = newBlog();
    (new LanguageFiller($blog))->fill();

    seedPublishedPosts(3, $blog);

    $job = new UpdateContentHtmlOfAllPostsJob($blog);
    $job->handle();

});