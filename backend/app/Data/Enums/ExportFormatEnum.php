<?php declare(strict_types=1);

namespace App\Data\Enums;

enum ExportFormatEnum : string
{
    case HYVOR_BLOGS = 'hyvor_blogs';
    case WORDPRESS = 'wordpress';
}
