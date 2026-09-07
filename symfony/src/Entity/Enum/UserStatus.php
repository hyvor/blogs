<?php

namespace App\Entity\Enum;

enum UserStatus: string
{
    case INVITED = 'invited';
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
}
