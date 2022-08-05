<?php

namespace Tests\Feature\ConsoleAPI\Billing;

use App\Domains\Subscription\SubscriptionService;
use Database\Factories\SubscriptionFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Paddle\Cashier;

it('updates the subscription', function () {
    Cashier::fake()->response('subscription/users/update', []);

    $blog = blog();

    (SubscriptionFactory::new())->create([
        'billable_id' => $blog->id,
        'paddle_plan' => SubscriptionService::paddlePlans()[0]->id,
        'paddle_status' => 'active',
    ]);

    $this->callConsoleApi('PATCH', '/billing/subscription', [
        'plan' => 'A',
        'frequency' => 'yearly',
    ])->assertOk();

    Http::assertSent(function (Request $request) use ($blog) {
        return str_ends_with($request->url(), 'subscription/users/update') &&
            $request['plan_id'] === SubscriptionService::paddlePlans()[1]->id &&
            $request['prorate'] === true &&
            $request['bill_immediately'] === true &&
            $request['subscription_id'] === $blog->subscription()->paddle_id;
    });
});

it('cannot update to the same plan', function () {
    Http::fake();

    $blog = blog();

    (SubscriptionFactory::new())->create([
        'billable_id' => $blog->id,
        'paddle_plan' => SubscriptionService::paddlePlans()[0]->id,
        'paddle_status' => 'active',
    ]);

    $this->callConsoleApi('PATCH', '/billing/subscription', [
        'plan' => 'A',
        'frequency' => 'monthly',
    ])
        ->assertUnprocessable()
        ->assertSee('Cannot update to the same plan');
});
