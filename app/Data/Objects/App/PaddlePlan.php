<?php

namespace App\Data\Objects\App;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;

class PaddlePlan
{
    public function __construct(
        public int $id,
        public SubscriptionPlanEnum $name,
        public SubscriptionFrequencyEnum $frequency,
        public int $price
    ) {
    }
}
