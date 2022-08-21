<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle\Webhook;

use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Integrations\Paddle\PaddleService;
use App\Models\Subscription;

it('cancels the subscription plan', function () {
    $blog = blog();

    $subscription = Subscription::factory()->create([
        'blog_id' => $blog
    ]);
    PaddleService::setPaddleSubscriptionId($subscription, 110);

    $this->callIntegrationEndpoint('POST', '/paddle/webhook', getPaddleWebhookParams([
        'alert_name' => 'subscription_cancelled',
        'subscription_id' => 110,
        'cancellation_effective_date' => '2022-08-16'
    ]))->assertOk();

    $subscription =  $blog->subscriptions[0];

    expect($subscription->status)->toBe(SubscriptionStatusEnum::DELETED);
    expect($subscription->ends_at->toDateString())->toBe('2022-08-16');
});
