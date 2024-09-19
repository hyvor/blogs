<?php

namespace Tests\Unit\Domains\Subscription;

use App\Data\Enums\SubscriptionPlanEnum;

it('is at least', function() {

    expect(SubscriptionPlanEnum::STARTER->isAtLeast(SubscriptionPlanEnum::STARTER))->toBeTrue();
    expect(SubscriptionPlanEnum::GROWTH->isAtLeast(SubscriptionPlanEnum::STARTER))->toBeTrue();
    expect(SubscriptionPlanEnum::STARTER->isAtLeast(SubscriptionPlanEnum::GROWTH))->toBeFalse();
    expect(SubscriptionPlanEnum::PREMIUM->isAtLeast(SubscriptionPlanEnum::GROWTH))->toBeTrue();

});