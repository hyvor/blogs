<?php

namespace Tests\Feature\ConsoleAPI\Billing;

use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Paddle\Cashier;

it('creates a PayLink for subscription', function () {
    $url = 'https://checkout.paddle.com/checkout/custom/eyJ0IjoiUHJvZ…….';
    Cashier::fake()->response('product/generate_pay_link', [
        'url' => $url,
    ]);

    $this->callConsoleApi('POST', '/billing/subscription', [
        'plan' => 'A',
        'frequency' => 'monthly',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('link', $url)
        );
});

it('validates', function () {
    $this->callConsoleApi('POST', '/billing/subscription', [
        'plan' => 'wrong',
    ])
        ->assertUnprocessable()
        ->assertSee('plan is invalid');
});
