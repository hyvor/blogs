<?php

namespace App\Data\Enums;

enum SeoExternalLinksFollowEnum : string
{
    case FOLLOW = 'follow';
    case NOFOLLOW = 'nofollow';
}
