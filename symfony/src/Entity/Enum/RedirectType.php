<?php

namespace App\Entity\Enum;

enum RedirectType: string
{
    case PERMANENT = 'permanent';
    case TEMPORARY = 'temporary';
}
