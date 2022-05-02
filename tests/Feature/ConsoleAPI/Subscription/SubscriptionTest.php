<?php

namespace Tests\Feature\ConsoleAPI\Subscription;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Domains\Subscription\SubscriptionRepository;

/**
 * Here we are not testing the Paddle API
 * as it involves making real HTTP calls
 *
 * We are testing what we can
 * Other things will be manual tests for now
 * To DO maybe use mocking for API calls
 */

it('gets plan config', function() {

    $plan = SubscriptionRepository::getPlanConfigFromPlanNameAndFrequency(
       SubscriptionPlanEnum::ENTERPRISE,
       SubscriptionFrequencyEnum::MONTHLY
    );

    $this->assertEquals($plan['name'], SubscriptionPlanEnum::ENTERPRISE);
    $this->assertEquals($plan['frequency'], SubscriptionFrequencyEnum::MONTHLY);

});