<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle;

use App\Models\Subscription;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('updates subscription', function () {
    Http::fake([
        'https://vendors.paddle.com/api/2.0/subscription/users/update' => Http::response([
            'success' => true,
            'response' => []
        ])
    ]);

    $blog = blogWithAccess();
    $subscription = Subscription::factory()->create([
        'blog_id' => $blog
    ]);
    $subscription->setMeta('paddle_subscription_id', 100);

    consoleApi($blog, 'PATCH', '/billing/paddle/subscription', [
        'frequency' => 'yearly',
        'plan' => 'team'
    ])->assertOk();

    Http::assertSent(function (Request $request) {
        return $request['subscription_id'] === 100 &&
            $request['plan_id'] === 32102;
    });
});

it('fails on no subscription', function () {
    $blog = blogWithAccess();
    consoleApi($blog, 'PATCH', '/billing/paddle/subscription', [
        'frequency' => 'yearly',
        'plan' => 'C'
    ])
        ->assertUnprocessable()
        ->assertSee('No current subscription');
});

it('fails on same subscription', function () {
    $blog = blogWithAccess();
    $subscription = Subscription::factory()->create([
        'blog_id' => $blog,
        'plan' => 'team',
        'frequency' => 'yearly'
    ]);

    consoleApi($blog, 'PATCH', '/billing/paddle/subscription', [
        'frequency' => 'yearly',
        'plan' => 'team'
    ])->assertUnprocessable()
        ->assertSee('Cannot be changed to the same subscription');
});

it('fails when paddle subscription ID is not set', function () {
    $blog = blogWithAccess();
    Subscription::factory()->create([
        'blog_id' => $blog
    ]);

    consoleApi($blog, 'PATCH', '/billing/paddle/subscription', [
        'frequency' => 'yearly',
        'plan' => 'team'
    ])
        ->assertUnprocessable()
        ->assertSee('Subscription ID is not set');
});
