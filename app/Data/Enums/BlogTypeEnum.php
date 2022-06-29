<?php

namespace App\Data\Enums;

enum BlogTypeEnum: string
{
    case DEFAULT = 'default';
    case DEV = 'dev';
    case PREVIEW = 'preview';
    case TEMP = 'temp';
}
