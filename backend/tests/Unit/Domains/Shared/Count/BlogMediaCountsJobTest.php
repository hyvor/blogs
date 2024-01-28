<?php

namespace Tests\Unit\Domains\Shared\Count;

use App\Domains\Shared\Count\BlogMediaCountsJob;
use App\Models\Media;

it('counts users', function () {
    $blog = blog();
    Media::factory()->count(3)->create(['blog_id' => $blog, 'size' => 1000]);

    BlogMediaCountsJob::dispatch($blog);

    $blog->refresh();
    expect($blog->getCount('media'))->toBe(3000);
});
