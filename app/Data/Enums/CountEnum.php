<?php
namespace App\Data\Enums;

/**
 * All possible count values for the Count model (counts table)
 * 
 * Values should be unique for each model.
 */
enum CountEnum : string {

    case BLOG_USERS = 'blog_users';
    case BLOG_POSTS = 'blog_posts';
    case BLOG_POSTS_DRAFT = 'blog_posts_draft';
    case BLOG_POSTS_SCHEDULED = 'blog_posts_scheduled';
    case BLOG_POSTS_FEATURED = 'blog_posts_featured';
    case BLOG_MEDIA = 'blog_media';

    case USER_POSTS = 'user_posts';

    case TAG_POSTS = 'tag_posts';

}