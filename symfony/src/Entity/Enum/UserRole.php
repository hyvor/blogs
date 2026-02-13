<?php

namespace App\Entity\Enum;

enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case WRITER = 'writer';
    case CONTRIBUTOR = 'contributor';
}