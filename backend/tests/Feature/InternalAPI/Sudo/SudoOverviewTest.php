<?php

namespace Feature\InternalApi\Sudo;

use App\Models\Blog;
use App\Models\Subscription;
use Hyvor\Internal\InternalApi\ComponentType;
use Hyvor\Internal\InternalApi\Testing\InternalApiTesting;

it('gets overview', function () {
    // blogs
    $blog1 = Blog::factory()->create([
        'id' => 2001,
        'created_at' => now()->subDays(value: 31),
        'trial_ends_at' => now()->subDays(10)
    ]);

    $blog2 = Blog::factory()->create([
        'id' => 2002,
        'created_at' => now()->subDays(10),
        'trial_ends_at' => now()->subDays(5)
    ]);

    $blog3 = Blog::factory()->create([
        'id' => 2003,
        'created_at' => now()->subDays(18),
        'trial_ends_at' => now()->addDays(18)
    ]);

    // subscriptions
    Subscription::factory()->count(2)->create(
        ['status' => 'active', 'plan' => 'starter', 'blog_id' => $blog1->id, 'frequency' => 'monthly']
    );
    Subscription::factory()->count(3)->create(
        ['status' => 'active', 'plan' => 'growth', 'blog_id' => $blog2->id, 'frequency' => 'monthly']
    );


    InternalApiTesting::call('GET', '/core/sudo/overview', from: ComponentType::CORE)
        ->assertOk()

        // blogs
        ->assertJsonPath('blogs.total', 3)
        ->assertJsonPath('blogs.total_30_days_change', 2)
        ->assertJsonPath('blogs.in_trial', 1)
        ->assertJsonPath('blogs.paid', 2)
        ->assertJsonPath('blogs.paid_30d_change', 2);
});
