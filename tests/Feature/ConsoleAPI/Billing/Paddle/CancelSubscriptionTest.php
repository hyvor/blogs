<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle;

use App\Models\Subscription;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('calls cancel endpoint', function () {
    Http::fake([
        'https://vendors.paddle.com/api/2.0/subscription/users_cancel' => Http::response([
            'success' => true
        ])
    ]);

    $blog = blogWithAccess();
    $subscription = Subscription::factory()->create([
        'blog_id' => $blog
    ]);
    $subscription->setMeta('paddle_subscription_id', 140);

    consoleApi($blog, 'DELETE', '/billing/paddle/subscription')
        ->assertOk();

    Http::assertSent(function (Request $request) {
        return $request['subscription_id'] === 140;
    });
});
