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

    $blog = blog();
    $subscription = Subscription::factory()->create([
        'blog_id' => $blog
    ]);
    $subscription->setMeta('paddle_subscription_id', 100);

    $this->callConsoleApi('PATCH', '/billing/paddle/subscription', [
        'frequency' => 'yearly',
        'plan' => 'C'
    ])->assertOk();

    Http::assertSent(function (Request $request) {
        return $request['subscription_id'] === 100 &&
            $request['plan_id'] === 32102;
    });
});

it('fails on no subscription', function () {
    $this->callConsoleApi('PATCH', '/billing/paddle/subscription', [
        'frequency' => 'yearly',
        'plan' => 'C'
    ])
        ->assertUnprocessable()
        ->assertSee('No current subscription');
});

it('fails on same subscription', function () {
    $blog = blog();
    $subscription = Subscription::factory()->create([
        'blog_id' => $blog,
        'plan' => 'C',
        'frequency' => 'yearly'
    ]);

    $this->callConsoleApi('PATCH', '/billing/paddle/subscription', [
        'frequency' => 'yearly',
        'plan' => 'C'
    ])->assertUnprocessable()
        ->assertSee('Cannot be changed to the same subscription');
});

it('fails when paddle subscription ID is not set', function () {
    $blog = blog();
    Subscription::factory()->create([
        'blog_id' => $blog
    ]);

    $this->callConsoleApi('PATCH', '/billing/paddle/subscription', [
        'frequency' => 'yearly',
        'plan' => 'C'
    ])
        ->assertUnprocessable()
        ->assertSee('Subscription ID is not set');
});
