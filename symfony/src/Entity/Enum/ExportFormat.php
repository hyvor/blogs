<?php

namespace App\Entity\Enum;

enum ExportFormat: string
{
    case HYVOR_BLOGS = 'hyvor_blogs';
    case WORDPRESS = 'wordpress';
}
