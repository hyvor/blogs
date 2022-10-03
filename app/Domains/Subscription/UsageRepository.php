<?php

namespace App\Domains\Subscription;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\ConsoleAPI\Billing\UsageObject;
use App\Models\Blog;

class UsageRepository
{
    public static function getUsage(Blog $blog): array
    {
        $limits = self::getLimits($blog);

        return [
            'users' => new UsageObject($blog->getCount('users'), $limits['users']),
            'media' => new UsageObject($blog->getCount('media'), $limits['media']),
        ];
    }

    private static function getLimits(Blog $blog): array
    {
        $subscription = SubscriptionService::getActiveBlogSubscription($blog);
        $plan = $subscription?->plan;

        $gb = (10 ** 9);

        $users = match ($plan) {
            SubscriptionPlanEnum::A => 2,
            SubscriptionPlanEnum::B => 10,
            SubscriptionPlanEnum::C => 100,
            SubscriptionPlanEnum::D => 1000,
            SubscriptionPlanEnum::E => 10000,
            default => 1
        };

        // bytes
        $media = match ($plan) {
            SubscriptionPlanEnum::A => 40 * $gb,
            SubscriptionPlanEnum::B => 250 * $gb,
            SubscriptionPlanEnum::C => 1000 * $gb,
            SubscriptionPlanEnum::D => 2000 * $gb,
            SubscriptionPlanEnum::E => 5000 * $gb,
            default => $gb
        };

        return [
            'users' => $users,
            'media' => $media,
        ];
    }
}
