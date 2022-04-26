<?php
namespace App\Data\Enums;

enum UserStatusEnum: string  {

    case INVITED = 'invited';
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';

}