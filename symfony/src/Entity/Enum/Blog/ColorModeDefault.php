<?php

namespace App\Entity\Enum\Blog;

enum ColorModeDefault: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
    case OS = 'os';
}
