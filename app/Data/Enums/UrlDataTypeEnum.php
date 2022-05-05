<?php

namespace App\Data\Enums;

enum UrlDataTypeEnum : string
{
    case LINK = 'link';
    case RICH = 'rich';
    case ERROR = 'error';
}
