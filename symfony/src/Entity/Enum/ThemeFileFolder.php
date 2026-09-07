<?php

namespace App\Entity\Enum;

enum ThemeFileFolder: string
{
    case TEMPLATES = 'templates';
    case ASSETS = 'assets';
    case STYLES = 'styles';
    case LANG = 'lang';
}
