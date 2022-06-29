<?php

namespace App\Data\Enums;

enum ImportFormatEnum: string
{
    case WORDPRESS = 'wordpress';
    case MEDIUM = 'medium';
    case GHOST = 'ghost';
    case HYVOR = 'hyvor';
    case BLOGGER = 'blogger';
    case TUMBLR = 'tumblr';
    case SUBSTACK = 'substack';
}
