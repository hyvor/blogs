<?php

namespace App\Data\Enums;

enum SubscriptionPlanEnum : string
{
    case PRO = 'pro';
    case TEAM = 'team';
    case ENTERPRISE = 'enterprise';
}
