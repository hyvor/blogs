<?php

namespace Tests\Feature\Integrations\Shopify;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Integrations\Shopify\ShopifyBillingDataEncoder;
use App\Domains\Subscription\SubscriptionService;
use Illuminate\Support\Facades\URL;

it('confirms the subscription', function () {
    $url = URL::signedRoute('shopify-billing-create', [
        'data' => ShopifyBillingDataEncoder::encode(
            config('test.blog_id'),
            SubscriptionPlanEnum::A,
            SubscriptionFrequencyEnum::MONTHLY
        )
    ]);
    $url .= '&charge_id=100';

    $this
        ->call('GET', $url)
        ->assertRedirect('/console/test/billing');

    $subscriptions = blog()->subscriptions;
    expect($subscriptions->count())->toBe(1);
    $subscription = $subscriptions[0];
    expect($subscription->plan)->toBe(SubscriptionPlanEnum::A);
    expect($subscription->frequency)->toBe(SubscriptionFrequencyEnum::MONTHLY);
    expect($subscription->getMeta('shopify_charge_id'))->toBe(100);
});

it('cancels the current subscription', function () {
    $blog = blog();

    $subscription = SubscriptionService::createSubscription(
        $blog,
        SubscriptionPlanEnum::D,
        SubscriptionFrequencyEnum::MONTHLY
    );

    $this
        ->callIntegrationEndpoint('GET', '/shopify/billing/confirm', [
            'charge_id' => 100,
            'data' => ShopifyBillingDataEncoder::encrypt(
                config('test.blog_id'),
                SubscriptionPlanEnum::A,
                SubscriptionFrequencyEnum::MONTHLY
            )
        ])
        ->assertRedirect('/console/test/billing');

    $subscription->refresh();
    expect($subscription->status)->toBe(SubscriptionStatusEnum::DELETED);
});
