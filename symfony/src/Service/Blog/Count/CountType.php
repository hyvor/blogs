<?php

namespace App\Service\Blog\Count;

enum CountType: string
{

    // posts_* in blog
    case POSTS = 'posts';

    // posts_count in users
    case AUTHORS = 'authors';

    // posts_count in tags
    case TAGS = 'tags';

    // users in blog
    case USERS = 'users';

    // media in blog
    case MEDIA = 'media';
}
