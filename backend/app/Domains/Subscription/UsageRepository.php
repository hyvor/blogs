<?php

namespace App\Domains\Subscription;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\ConsoleAPI\Billing\UsageObject;
use App\Domains\Integrations\DeepL\DeepLService;
use App\Domains\Integrations\OpenAi\GptPromptsService;
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
            'auto_translate' => new UsageObject(DeepLService::getThisMonthUsage($blog), $limits['auto_translate']),
            'gpt' => new UsageObject(GptPromptsService::getThisMonthUsage($blog), $limits['gpt'])
        ];
    }

    /**
     * @param Blog $blog
     * @return array{users: int, media: int, auto_translate: int, gpt: int}
     */
    private static function getLimits(Blog $blog): array
    {

        $license = LicenseService::getLicense($blog);

        $gb = (10 ** 9);

        $users = match ($plan) {
            SubscriptionPlanEnum::GROWTH => 5,
            SubscriptionPlanEnum::PREMIUM => 15,
            SubscriptionPlanEnum::TEAM => 100,
            default => 2
        };

        // bytes
        $media = match ($plan) {
            SubscriptionPlanEnum::STARTER => $gb,
            SubscriptionPlanEnum::GROWTH => 40 * $gb,
            SubscriptionPlanEnum::PREMIUM => 250 * $gb,
            SubscriptionPlanEnum::TEAM => 1000 * $gb,
            default => $gb
        };

        $autoTranslateChars = DeepLService::getMaxCharsPerMonth($blog, $plan);
        $gptTokens = GptPromptsService::getMaxMonthlyGptTokens($blog, $plan);

        return [
            'users' => $license->users,
            'media' => $media,
            'auto_translate' => $autoTranslateChars,
            'gpt' => $gptTokens
        ];
    }

    public static function getLimitsOf(Blog $blog, string $type): int
    {
        $limits = self::getLimits($blog);
        return $limits[$type];
    }



}
