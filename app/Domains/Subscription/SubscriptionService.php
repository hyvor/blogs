<?php

namespace App\Domains\Subscription;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Models\Blog;
use App\Models\Subscription;
use DateTimeInterface;

class SubscriptionService
{

    public static function createSubscription(
        Blog $blog,
        SubscriptionPlanEnum $plan,
        SubscriptionFrequencyEnum $frequency,
        SubscriptionStatusEnum $status = SubscriptionStatusEnum::ACTIVE,
        ?DateTimeInterface $endsAt = null
    ) : Subscription
    {

        return Subscription::create([
            'blog_id' => $blog->id,
            'plan' => $plan,
            'frequency' => $frequency,
            'status' => $status,
            'ends_at' => $endsAt
        ]);

    }

    /**
     * @param array{plan?: SubscriptionPlanEnum, frequency?: SubscriptionFrequencyEnum, status?: SubscriptionStatusEnum} $updates
     */
    public static function updateSubscription(Subscription $subscription, array $updates) : Subscription
    {
        foreach ($updates as $key => $value) {
            $subscription->$key = $value;
        }

        $subscription->save();

        return $subscription;
    }

    public static function cancelSubscription(Subscription $subscription, DateTimeInterface $date) : Subscription
    {
        $subscription->ends_at = $date;
        $subscription->save();

        return $subscription;
    }

}
