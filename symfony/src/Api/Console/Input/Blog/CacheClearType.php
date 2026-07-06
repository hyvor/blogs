<?php

namespace App\Api\Console\Input\Blog;

enum CacheClearType: string
{
    case ALL = 'all';
    case TEMPLATE = 'template';
    case PATHS = 'paths';
}
