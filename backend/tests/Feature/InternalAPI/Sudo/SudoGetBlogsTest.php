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


it('filters blog by subdomain', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subdomain' => 'sub1'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['subdomain' => $blog1->subdomain]
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.subdomain', $blog1->subdomain);
});

it('filters by in trial', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->addDays(10),
    ]);

    $blog2 = Blog::factory()->create([
        'id' => 2002,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'in_trial']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('1.id', $blog1->id);
});

it('filters by starer plan', function () {
    $blogs = Blog::factory()->count(3)->create();

    Subscription::factory()->create([
        'blog_id' => $blogs[0]->id,
        'plan' => 'starter'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'starter']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blogs[0]->id);
});

it('filter by growth plan', function () {
    $blogs = Blog::factory()->count(3)->create();

    Subscription::factory()->create([
        'blog_id' => $blogs[0]->id,
        'plan' => 'growth'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'growth']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blogs[0]->id);

});

it('filter by premium plan', function () {
    $blogs = Blog::factory()->count(3)->create();

    Subscription::factory()->create([
        'blog_id' => $blogs[0]->id,
        'plan' => 'premium'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'premium']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blogs[0]->id);
});

it('filter by business plan', function () {
    $blogs = Blog::factory()->count(3)->create();

    Subscription::factory()->create([
        'blog_id' => $blogs[0]->id,
        'plan' => 'business'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'business']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blogs[0]->id);

});

it('filter by entreprise plan ', function () {
    $blogs = Blog::factory()->count(3)->create();

    Subscription::factory()->create([
        'blog_id' => $blogs[0]->id,
        'plan' => 'entreprise'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'entreprise']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blogs[0]->id);

});

it('filter by team plan ', function () {
    $blogs = Blog::factory()->count(3)->create();

    Subscription::factory()->create([
        'blog_id' => $blogs[0]->id,
        'plan' => 'team'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'team']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blogs[0]->id);

});