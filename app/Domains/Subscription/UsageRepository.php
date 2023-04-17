<?php

namespace App\Domains\Subscription;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\ConsoleAPI\Billing\UsageObject;
use App\Domains\Integrations\DeepL\DeepLService;
use App\Models\Blog;

class UsageRepository
{

    /**
     * @param Blog $blog
     * @return UsageObject[]
     */
    public static function getUsage(Blog $blog): array
    {
        $limits = self::getLimits($blog);

        return [
            'users' => new UsageObject($blog->getCount('users'), $limits['users']),
            'media' => new UsageObject($blog->getCount('media'), $limits['media']),
            'auto_translate' => new UsageObject(DeepLService::getThisMonthUsage($blog), $limits['auto_translate'])
        ];
    }

    /**
     * @param Blog $blog
     * @return array{users: int, media: int, auto_translate: int}
     */
    private static function getLimits(Blog $blog): array
    {
        $subscription = SubscriptionService::getActiveBlogSubscription($blog);
        $plan = $subscription?->plan;

        $gb = (10 ** 9);

        $users = match ($plan) {
            SubscriptionPlanEnum::A => 5,
            SubscriptionPlanEnum::B => 15,
            SubscriptionPlanEnum::C => 100,
            SubscriptionPlanEnum::D => 1000,
            SubscriptionPlanEnum::E => 10000,
            default => 2
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

        $autoTranslateChars = match ($plan) {
            SubscriptionPlanEnum::A => 100000,
            SubscriptionPlanEnum::B => 300000,
            SubscriptionPlanEnum::C => 1000000,
            SubscriptionPlanEnum::D => 5000000,
            SubscriptionPlanEnum::E => 10000000,
            default => 0
        };

        return [
            'users' => $users,
            'media' => $media,
            'auto_translate' => $autoTranslateChars,
        ];
    }

}
