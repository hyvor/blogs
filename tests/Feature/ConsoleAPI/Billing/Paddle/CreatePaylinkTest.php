<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle;

use App\Models\Subscription;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a Pay Link', function () {
    $link = 'https://example.com/paylink';

    $blog = blogWithAccess();

    Http::fake([
        'https://vendors.paddle.com/api/2.0/product/generate_pay_link' => Http::response([
            'success' => true,
            'response' => [
                'url' => $link
            ]
        ])
    ]);

    consoleApi($blog, 'POST', '/billing/paddle/subscription', [
        'plan' => 'starter',
        'frequency' => 'monthly'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('link', $link));
});

it('cannot create Pay Link when the blog already has', function () {

    $blog = blogWithAccess();

    Subscription::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'POST', '/billing/paddle/subscription', [
        'plan' => 'A',
        'frequency' => 'monthly'
    ])->assertUnprocessable();
});