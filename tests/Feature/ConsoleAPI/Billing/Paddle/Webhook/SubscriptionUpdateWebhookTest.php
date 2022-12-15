<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle\Webhook;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Integrations\Paddle\PaddleService;
use App\Models\Subscription;

it('updates a subscription plan', function () {
    $blog = blog();

    $subscription = Subscription::factory()->create([
        'blog_id' => $blog
    ]);
    PaddleService::setPaddleSubscriptionId($subscription, 110);

    integrationApi('POST', '/paddle/webhook', getPaddleWebhookParams([
        'alert_name' => 'subscription_updated',
        'subscription_plan_id' => PaddleService::planConfig(
            SubscriptionPlanEnum::D,
            SubscriptionFrequencyEnum::YEARLY
        )->id,
        'subscription_id' => 110
    ]))->assertOk();

    $subscription =  $blog->subscriptions[0];

    expect($subscription->status)->toBe(SubscriptionStatusEnum::ACTIVE);
    expect($subscription->plan)->toBe(SubscriptionPlanEnum::D);
    expect($subscription->frequency)->toBe(SubscriptionFrequencyEnum::YEARLY);
});

it('updates the status', function () {
    $blog = blog();

    $subscription = Subscription::factory()->create([
        'blog_id' => $blog
    ]);
    PaddleService::setPaddleSubscriptionId($subscription, 110);

    integrationApi('POST', '/paddle/webhook', getPaddleWebhookParams([
        'alert_name' => 'subscription_updated',
        'subscription_id' => 110,
        'status' => 'past_due'
    ]))->assertOk();


    $subscription =  $blog->subscriptions[0];

    expect($subscription->status)->toBe(SubscriptionStatusEnum::PAST_DUE);
});

it('past_due to active', function () {
    $blog = blog();

    $subscription = Subscription::factory()->create([
        'blog_id' => $blog,
        'status' => SubscriptionStatusEnum::PAST_DUE
    ]);
    PaddleService::setPaddleSubscriptionId($subscription, 110);

    integrationApi('POST', '/paddle/webhook', getPaddleWebhookParams([
        'alert_name' => 'subscription_updated',
        'subscription_id' => 110,
        'status' => 'active'
    ]))->assertOk();


    $subscription =  $blog->subscriptions[0];

    expect($subscription->status)->toBe(SubscriptionStatusEnum::ACTIVE);
});
