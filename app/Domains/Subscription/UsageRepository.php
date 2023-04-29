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
        $plan = $subscription?->plan ?? SubscriptionPlanEnum::STARTER;

        $gb = (10 ** 9);

        $users = match ($plan) {
            SubscriptionPlanEnum::STARTER => 2,
            SubscriptionPlanEnum::GROWTH => 5,
            SubscriptionPlanEnum::PREMIUM => 15,
            SubscriptionPlanEnum::TEAM => 100,
            SubscriptionPlanEnum::BUSINESS => 1000,
            SubscriptionPlanEnum::ENTERPRISE => 10000,
        };

        // bytes
        $media = match ($plan) {
            SubscriptionPlanEnum::STARTER => $gb,
            SubscriptionPlanEnum::GROWTH => 40 * $gb,
            SubscriptionPlanEnum::PREMIUM => 250 * $gb,
            SubscriptionPlanEnum::TEAM => 1000 * $gb,
            SubscriptionPlanEnum::BUSINESS => 2000 * $gb,
            SubscriptionPlanEnum::ENTERPRISE => 5000 * $gb,
        };

        $autoTranslateChars = DeepLService::getMaxCharsPerMonth($plan);

        return [
            'users' => $users,
            'media' => $media,
            'auto_translate' => $autoTranslateChars,
        ];
    }

}
