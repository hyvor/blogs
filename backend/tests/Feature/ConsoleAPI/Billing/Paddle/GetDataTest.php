<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle;

use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets info and receipts', function () {
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
        ]),
        'https://vendors.paddle.com/api/2.0/subscription/users' => Http::response([
            'success' => true,
            'response' => [
                [
                    'subscription_id' => 502198,
                    'plan_id' => 496199,
                    'user_id' => 285846,
                    'user_email' => 'name@example.com',
                    'marketing_consent' => true,
                    'update_url' => 'https://subscription-management.paddle.com/subscription/87654321/hash/eyJpdiI6IlU0Nk5cL1JZeHQyTXd.../update',
                    'cancel_url' => 'https://subscription-management.paddle.com/subscription/87654321/hash/eyJpdiI6IlU0Nk5cL1JZeHQyTXd.../cancel',
                    'state' => 'active',
                    'signup_date' => '2015-10-06 09:44:23',
                    'last_payment' => [
                        'amount' => 5,
                        'currency' => 'USD',
                        'date' => '2015-10-06',
                    ],
                    'payment_information' => [
                        'payment_method' => 'card',
                        'card_type' => 'visa',
                        'last_four_digits' => '1111',
                        'expiry_date' => '02/2020',
                    ],
                    'quantity' => 3,
                    'next_payment' => [
                        'amount' => 10,
                        'currency' => 'USD',
                        'date' => '2015-11-06',
                    ],
                ],
            ],
        ])
    ]);

    $blog = blogWithAccess();

    $subscription = Subscription::factory()->create(['blog_id' => $blog]);
    $subscription->setMeta('paddle_subscription_id', 1);

    consoleApi($blog, 'GET', '/billing/paddle')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has('info', function (AssertableJson $json) {
                    $json->where('email', 'name@example.com')
                        ->where('last_payment.amount', 5)
                        ->where('last_payment.currency', 'USD')
                        ->has('last_payment.at')
                        ->etc();
                })
                ->has('payments', 1, function (AssertableJson $json) {
                    $json->where('id', 8936)
                        ->has('paid_at')
                        ->where('amount', 1)
                        ->where('currency', 'USD')
                        ->has('receipt_url');
                });
        });
});


it('returns empty when paddle ID is not set', function() {

    $blog = blogWithAccess();
    Subscription::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'GET', '/billing/paddle')
        ->assertOk()
        ->assertJsonPath('info', null)
        ->assertJsonPath('payments', []);

});