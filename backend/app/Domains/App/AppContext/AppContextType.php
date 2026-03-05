<?php

namespace App\Domains\App\AppContext;

// note: a very bad design
enum AppContextType : string
{

    // used when a blog is seeding
    case SEEDING_BLOG = 'seeding_blog';

}
