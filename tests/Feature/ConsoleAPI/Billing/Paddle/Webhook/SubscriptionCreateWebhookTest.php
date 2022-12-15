<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle\Webhook;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Integrations\Paddle\PaddleService;
use App\Domains\Integrations\Paddle\Passthrough\Passthrough;

it('creates a subscription', function () {
    $blog = blog();
    integrationApi('POST', '/paddle/webhook', getPaddleWebhookParams([
        'alert_name' => 'subscription_created',
        'passthrough' => Passthrough::encode($blog),
        'subscription_plan_id' => PaddleService::paddlePlans()[0]->id,
        'subscription_id' => 1200
    ]))->assertOk();

    expect($blog->subscriptions()->count())->toBe(1);

    $subscription =  $blog->subscriptions[0];

    expect($subscription->status)->toBe(SubscriptionStatusEnum::ACTIVE);
    expect($subscription->plan)->toBe(SubscriptionPlanEnum::A);
    expect($subscription->frequency)->toBe(SubscriptionFrequencyEnum::MONTHLY);
    expect($subscription->getMeta('paddle_subscription_id'))->toBe(1200);
});
