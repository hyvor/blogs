<?php

namespace Tests\Unit\Domains\Subscription;

use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Subscription\SubscriptionService;
use App\Models\Subscription;

test('subscription valid', function() {

    // active
    expect(SubscriptionService::isSubscriptionActive(Subscription::factory()->create([
        'status' => SubscriptionStatusEnum::ACTIVE,
    ])))->toBe(true);

    // past_due
    expect(SubscriptionService::isSubscriptionActive(Subscription::factory()->create([
        'status' => SubscriptionStatusEnum::PAST_DUE,
    ])))->toBe(true);

    // deleted (ends_at null, usually cannot happen)
    expect(SubscriptionService::isSubscriptionActive(Subscription::factory()->create([
        'status' => SubscriptionStatusEnum::DELETED,
        'ends_at' => null
    ])))->toBe(false);


    // deleted (grace period)
    expect(SubscriptionService::isSubscriptionActive(Subscription::factory()->create([
        'status' => SubscriptionStatusEnum::DELETED,
        'ends_at' => now()->addDays(2)
    ])))->toBe(true);

    // deleted (grace period ended)
    expect(SubscriptionService::isSubscriptionActive(Subscription::factory()->create([
        'status' => SubscriptionStatusEnum::DELETED,
        'ends_at' => now()->subDay()
    ])))->toBe(false);

});