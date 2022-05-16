<?php

namespace App\Data\Enums;

enum UserRoleEnum: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case WRITER = 'writer';
    case CONTRIBUTOR = 'contributor';
    case FINANCE = 'finance';
}
