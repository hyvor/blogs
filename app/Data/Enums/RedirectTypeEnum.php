<?php

namespace App\Data\Enums;

enum RedirectTypeEnum: string
{
    case PERMANENT = 'permanent';
    case TEMPORARY = 'temporary';
}
