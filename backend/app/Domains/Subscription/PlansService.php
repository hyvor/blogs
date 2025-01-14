<?php

namespace App\Domains\Subscription;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;

class PlansService
{
    public static function getPlanPrice(SubscriptionPlanEnum $plan, SubscriptionFrequencyEnum $frequency) : int
    {
        $price = config('blogs.pricing')[$plan->value];
        return $price * ($frequency === SubscriptionFrequencyEnum::YEARLY ? 10 : 1);
    }

    public static function getMonthlyPrice(SubscriptionPlanEnum $plan) : int
    {
        return self::getPlanPrice($plan, SubscriptionFrequencyEnum::MONTHLY);
    }
}
