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

it('filters by in trial', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub1'
    ]);

    $blog2 = Blog::factory()->create([
        'id' => 2002,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub2'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'in_trial']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blog1->id);
});

it('filters by starer plan', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub1'
    ]);

    $blog2 = Blog::factory()->create([
        'id' => 2002,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub2'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog1->id,
        'plan' => 'starter'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog2->id,
        'plan' => 'growth'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'starter']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blog1->id);
});

it('filter by growth plan', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub1'
    ]);

    $blog2 = Blog::factory()->create([
        'id' => 2002,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub2'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog1->id,
        'plan' => 'starter'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog2->id,
        'plan' => 'growth'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'growth']
    )
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $blog2->id);
});

it('filter by premium plan', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub1'
    ]);

    $blog2 = Blog::factory()->create([
        'id' => 2002,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub2'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog1->id,
        'plan' => 'starter'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog2->id,
        'plan' => 'premium'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'premium']
    )
        ->assertOk()
        ->assertJsonPath('0.id', $blog2->id);
});

it('filter by business plan', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub1'
    ]);

    $blog2 = Blog::factory()->create([
        'id' => 2002,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub2'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog1->id,
        'plan' => 'starter'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog2->id,
        'plan' => 'business'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'business']
    )
        ->assertOk()
        ->assertJsonPath('0.id', $blog2->id);
});

it('filter by entreprise plan ', function () {
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub1'
    ]);

    $blog2 = Blog::factory()->create([
        'id' => 2002,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10),
        'subomain' => 'sub2'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog1->id,
        'plan' => 'starter'
    ]);

    Subscription::factory()->create([
        'blog_id' => $blog2->id,
        'plan' => 'enterprise'
    ]);

    InternalApiTesting::call(
        'GET',
        '/core/sudo/blogs',
        ['filter' => 'enterprise']
    )
        ->assertOk()
        ->assertJsonPath('0.id', $blog2->id);
});