<?php
namespace App\Data\Enums;

/**
 * All possible count values for the Count model (counts table)
 * 
 * Values should be unique for each model.
 */
enum CountEnum : string {

    case BLOG_USERS = 'users';
    case BLOG_POSTS = 'posts';
    case BLOG_MEDIA = 'media';
    case BLOG_TAGS = 'tags';

}