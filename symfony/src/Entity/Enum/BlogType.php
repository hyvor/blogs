<?php

namespace App\Entity\Enum;

enum BlogType: string
{
    case DEFAULT = 'default';
    case DEV = 'dev';
    case PREVIEW = 'preview';
    case TEMP = 'temp';
}
