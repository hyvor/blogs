<?php declare(strict_types=1);

namespace App\Data\Enums;

enum SubscriptionPlanEnum: string
{
    case STARTER = 'starter';
    case GROWTH = 'growth';
    case PREMIUM = 'premium';
    case TEAM = 'team';

    public function isAtLeast(SubscriptionPlanEnum $plan): bool
    {
        $cases = self::cases();
        return array_search($this, $cases) >= array_search($plan, $cases);
    }

    public function toHumanReadable(): string
    {
        return match ($this) {
            self::STARTER => 'Starter',
            self::GROWTH => 'Growth',
            self::PREMIUM => 'Premium',
            self::TEAM => 'Team',
        };
    }

}
