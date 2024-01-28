<?php

namespace Tests\Unit\Domains\Shared\Count;

use App\Domains\Shared\Count\BlogUsersCountsJob;
use App\Models\User;

it('counts users', function () {
    $blog = blog();
    User::factory()->count(3)->create(['blog_id' => $blog]);

    BlogUsersCountsJob::dispatch($blog);

    $blog->refresh();
    expect($blog->getCount('users'))->toBe(3);
});
