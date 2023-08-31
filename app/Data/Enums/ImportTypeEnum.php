<?php declare(strict_types=1);

namespace App\Data\Enums;

enum ImportTypeEnum : string
{
    case SITEMAP = 'sitemap';
    case WORDPRESS = 'wordpress';
}
