<?php

namespace App\Entity\Enum;

enum CustomDomainStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
}
