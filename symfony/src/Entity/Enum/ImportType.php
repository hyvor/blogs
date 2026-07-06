<?php

namespace App\Entity\Enum;

enum ImportType: string
{
    case SITEMAP = 'sitemap';
    case WORDPRESS = 'wordpress';
}
