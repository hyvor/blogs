<?php

namespace App\Entity\Enum\Blog;

enum SeoExternalLinksFollow: string
{
    case FOLLOW = 'follow';
    case NOFOLLOW = 'nofollow';
}
