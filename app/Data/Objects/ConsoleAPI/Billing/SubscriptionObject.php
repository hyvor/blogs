<?php

namespace App\Data\Objects\ConsoleAPI\Billing;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Models\Subscription;

class SubscriptionObject
{
    public SubscriptionStatusEnum $status;

    public SubscriptionPlanEnum $plan;

    public SubscriptionFrequencyEnum $frequency;

    public int $created_at;

    public ?int $ends_at;

    public function __construct(Subscription $subscription)
    {
        $this->status = $subscription->status;
        $this->plan = $subscription->plan;
        $this->frequency = $subscription->frequency;
        $this->created_at = $subscription->created_at->timestamp;
        $this->ends_at = $subscription->ends_at?->timestamp;
    }
}
