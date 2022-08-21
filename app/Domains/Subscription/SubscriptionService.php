<?php

namespace App\Domains\Subscription;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Models\Blog;
use App\Models\Subscription;
use DateTimeInterface;
use Illuminate\Support\Collection;

class SubscriptionService
{
    /**
     * @param Blog $blog
     * @return Collection<Subscription>
     */
    public static function getAllSubscriptions(Blog $blog): Collection
    {
        return $blog->subscriptions;
    }

    public static function getActiveBlogSubscription(Blog $blog): ?Subscription
    {
        $subscription = $blog->subscriptions()->first();

        if (!$subscription) {
            return null;
        }

        return self::isSubscriptionActive($subscription) ? $subscription : null;
    }

    public static function isBlogSubscribed(Blog $blog): bool
    {
        $subscription = $blog->subscriptions()->first();

        if (!$subscription) {
            return false;
        }

        return self::isSubscriptionActive($subscription);
    }

    public static function isSubscriptionActive(Subscription $subscription): bool
    {
        if (
            $subscription->status === SubscriptionStatusEnum::ACTIVE ||
            $subscription->status === SubscriptionStatusEnum::PAST_DUE
        ) {
            return true;
        }

        return
            $subscription->status === SubscriptionStatusEnum::DELETED &&
            $subscription->ends_at &&
            $subscription->ends_at->greaterThan(now());
    }

    public static function createSubscription(
        Blog $blog,
        SubscriptionPlanEnum $plan,
        SubscriptionFrequencyEnum $frequency,
        SubscriptionStatusEnum $status = SubscriptionStatusEnum::ACTIVE,
        ?DateTimeInterface $endsAt = null
    ): Subscription {
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
    public static function updateSubscription(Subscription $subscription, array $updates): Subscription
    {
        foreach ($updates as $key => $value) {
            $subscription->$key = $value;
        }

        $subscription->save();

        return $subscription;
    }

    public static function cancelSubscription(Subscription $subscription, DateTimeInterface $date): Subscription
    {
        $subscription->ends_at = $date;
        $subscription->status = SubscriptionStatusEnum::DELETED;
        $subscription->save();

        return $subscription;
    }
}
