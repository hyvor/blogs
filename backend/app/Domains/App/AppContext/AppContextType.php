<?php

namespace App\Domains\App\AppContext;

enum AppContextType : string
{

    // used when a blog is seeding
    case SEEDING_BLOG = 'seeding_blog';

}