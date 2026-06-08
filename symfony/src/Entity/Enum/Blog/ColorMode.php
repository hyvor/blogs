<?php

namespace App\Entity\Enum\Blog;

enum ColorMode: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
    case BOTH = 'both';
}
