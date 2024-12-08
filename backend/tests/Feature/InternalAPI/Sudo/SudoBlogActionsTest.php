<?php

namespace Feature\InternalApi\Sudo;

use App\Models\Blog;
use Carbon\Carbon;
use Hyvor\Internal\InternalApi\Testing\InternalApiTesting;

it('updates trial date', function () {
    $blog = Blog::factory()->create();

    $tmsp = Carbon::parse('2025-12-15')->getTimestamp();

    InternalApiTesting::call(
        'POST',
        '/core/sudo/post/' . $blog->id,
        [
            'action' => 'update_trial',
            'trial_ends_at' => $tmsp,
        ]
    )
        ->assertOk();

    $blog->refresh();
    expect($blog->trial_ends_at)->toStartWith('2025-12-15');
});


it('block blog', function () {
    $blog = Blog::factory()->create();

    InternalApiTesting::call(
        'POST',
        '/core/sudo/post/' . $blog->id,
        [
            'action' => 'block',
        ]
    )
        ->assertOk();

    $blog->refresh();
    expect($blog->is_blocked)->toBeTrue();
    expect($blog->is_blocked_at)->not->toBeNull();
});

it('unlock blog', function () {
    $blog = Blog::factory()->create(['is_blocked' => true]);

    InternalApiTesting::call(
        'POST',
        '/core/sudo/post/' . $blog->id,
        [
            'action' => 'unlock',
        ]
    )
        ->assertOk();

    $blog->refresh();
    expect($blog->is_blocked)->toBeFalse();
    expect($blog->is_blocked_at)->toBeNull();
});
