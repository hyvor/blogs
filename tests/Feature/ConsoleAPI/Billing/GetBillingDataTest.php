<?php

namespace Tests\Feature\ConsoleAPI\Billing;

use Database\Factories\ReceiptFactory;
use Database\Factories\SubscriptionFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Paddle\Cashier;

it('gets billing data', function() {

    Cashier::fake()->response('subscription/users', [
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
        ]
    ]);

    $blog = blog();

    // 3 receipts
    (ReceiptFactory::new())->count(3)->create([
        'billable_id' => $blog->id
    ]);

    // 3 subscriptions
    (SubscriptionFactory::new())->count(3)->create([
        'billable_id' => $blog->id
    ]);

    $this->callConsoleApi('GET', '/billing')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {

            $json
                ->has('receipts', 3, function (AssertableJson $json) {
                    $json->has('id')
                        ->has('paid_at')
                        ->has('amount')
                        ->has('tax')
                        ->has('receipt_url');
                })
                ->has('subscriptions', 3, function (AssertableJson $json) {
                    $json->has('status')
                        ->has('quantity')
                        ->has('plan')
                        ->has('frequency')
                        ->has('created_at')
                        ->has('ends_at')
                        ->has('is_on_grace_period');
                })
                ->has('info', function (AssertableJson $json) {
                    $json->has('email')
                        ->has('card_brand')
                        ->has('card_last_four')
                        ->has('card_expiration')
                        ->has('update_url')
                        ->has('last_payment')
                        ->has('last_payment_at')
                        ->has('next_payment')
                        ->has('next_payment_at');
                })
                ->has('usage', function (AssertableJson $json) {
                    $json->has('users')
                        ->has('media');
                });

        });

});