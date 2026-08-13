<?php

namespace App\Entity\Enum;

enum CustomDomainTlsProvider: string
{
    case AUTO = 'auto';
    case CUSTOM = 'custom';
}
