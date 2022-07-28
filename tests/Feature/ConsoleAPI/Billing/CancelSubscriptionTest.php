<?php

namespace Tests\Feature\ConsoleAPI\Billing;

use Database\Factories\SubscriptionFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Paddle\Cashier;

it('cancels subscription', function() {

    Cashier::fake()
        ->response('subscription/users_cancel', [])
        // required to get next payment date
        ->response('subscription/users', [
            [
                'next_payment' => [
                    'amount' => 10,
                    'currency' => 'USD',
                    'date' => now()->addDays(7)->toDateString(),
                ],
            ]
        ]);

    $blog = blog();

    $subscription = (SubscriptionFactory::new())->create([
        'billable_id' => $blog->id,
        'paddle_plan' => config('blogs.paddle_plans')[0]->id,
        'paddle_status' => 'active'
    ]);

    $this->callConsoleApi('DELETE', '/billing/subscription')->assertOk();

    Http::assertSent(function (Request $request) use ($subscription) {
        return str_ends_with($request->url(), 'subscription/users_cancel') &&
            $request['subscription_id'] === $subscription->paddle_id;
    });

    expect($blog->subscription()->ends_at->greaterThan(now()))->toBeTrue();

});

it('cancels subscription forced', function() {

    $blog = blog();

    (SubscriptionFactory::new())->create([
        'billable_id' => $blog->id,
        'paddle_plan' => config('blogs.paddle_plans')[0]->id,
        'paddle_status' => 'cancelled',
        'ends_at' => now()->addDays(7)
    ]);

    $this->callConsoleApi('DELETE', '/billing/subscription', [
        'forced' => true
    ])->assertOk();

    Http::assertNothingSent();

    expect($blog->subscription()->ends_at->lessThanOrEqualTo(now()))->toBeTrue();

});