<?php

namespace App\Service\Blog\Count;

enum CountType: string
{

    // posts_* in blog
    case POSTS_OF_BLOG = 'posts_of_blog';

    // users in blog
    case USERS_OF_BLOG = 'users_of_blog';

    // media in blog
    case MEDIA_OF_BLOG = 'media_of_blog';

    // posts_count in users
    case POSTS_OF_USERS = 'posts_of_users';

    // posts_count in tags
    case POSTS_OF_TAGS = 'posts_of_tags';
}
