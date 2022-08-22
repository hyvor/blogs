<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets info and receipts', function() {

    Http::fake([
        'https://vendors.paddle.com/api/2.0/subscription/payments' => Http::response([
            'success' => true,
            'response' => [
                [
                    "id" => 8936,
                    "subscription_id" => 2746,
                    "amount" => 1,
                    "currency" => "USD",
                    "payout_date" => "2015-10-15",
                    "is_paid" => 0,
                    "is_one_off_charge" => false,
                    "receipt_url" => "https://my.paddle.com/receipt/469214-8936/1940881-chrea0eb34164b5-f0d6553bdf",
                ]
            ]
        ])
    ]);

    $subscription = Subscription::factory()->create(['blog_id' => blog()]);
    $subscription->setMeta('paddle_subscription_id', 1);

    $this->callConsoleApi('GET', '/billing/paddle')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {

            $json
                ->has('info')
                ->has('payments', 1, function (AssertableJson $json) {
                    $json->where('id', 8936)
                        ->has('paid_at')
                        ->where('amount', 1)
                        ->where('currency', 'USD')
                        ->has('receipt_url');
                });

        });

});