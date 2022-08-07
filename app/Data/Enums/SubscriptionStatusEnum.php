<?php

namespace App\Data\Enums;

enum SubscriptionStatusEnum : string
{
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case DELETED = 'deleted';
}
