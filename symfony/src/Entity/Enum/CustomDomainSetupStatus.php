<?php

namespace App\Entity\Enum;

enum CustomDomainSetupStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case FAILED = 'failed';
}
