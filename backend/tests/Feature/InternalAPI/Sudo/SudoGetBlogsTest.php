<?php

namespace Feature\InternalApi\Sudo;

use App\Models\Blog;
use App\Models\Subscription;
use Hyvor\Internal\InternalApi\Testing\InternalApiTesting;

it('gets blogs', function () {
    Blog::factory()->count(3)->create();

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
    )
        ->assertOk()
        ->assertJsonCount(3);
});

it('filters blog by id', function () {
    $blogs = Blog::factory()->count(3)->create();

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['blog_id' => $blogs[1]->id]
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blogs[1]->id);
});


it('filters blog by subomain', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub1'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['subdomain' => $blog1->subomain]
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blog1->subomain);
});

