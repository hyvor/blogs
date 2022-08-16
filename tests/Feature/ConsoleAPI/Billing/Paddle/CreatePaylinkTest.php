<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle;

use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a Pay Link', function() {

    $link = 'https://example.com/paylink';

    Http::fake([
        'https://vendors.paddle.com/api/2.0/product/generate_pay_link' => Http::response([
            'success' => true,
            'response' => [
                'url' => $link
            ]
        ])
    ]);

    $this->callConsoleApi('POST', '/billing/paddle/subscription', [
        'plan' => 'A',
        'frequency' => 'monthly'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('link', $link));

});

it('cannot create Pay Link when the blog already has', function() {

    Subscription::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('POST', '/billing/paddle/subscription', [
        'plan' => 'A',
        'frequency' => 'monthly'
    ])->assertUnprocessable();

});