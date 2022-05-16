<?php

namespace App\Data\Enums;

enum ThemeFileFolderEnum: string
{
    case TEMPLATES = 'templates';
    case ASSETS = 'assets';
    case STYLES = 'styles';
    case LANG = 'lang';
}
