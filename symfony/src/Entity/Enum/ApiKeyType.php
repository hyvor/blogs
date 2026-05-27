<?php

namespace App\Entity\Enum;

enum ApiKeyType: string
{
    case CONSOLE = 'console';
    case DELIVERY = 'delivery';
}
