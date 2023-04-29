<?php declare(strict_types=1);

namespace App\Data\Enums;

enum SubscriptionPlanEnum: string
{
    case STARTER = 'starter';
    case GROWTH = 'growth';
    case PREMIUM = 'premium';
    case TEAM = 'team';
    case BUSINESS = 'business';
    case ENTERPRISE = 'enterprise';
}
