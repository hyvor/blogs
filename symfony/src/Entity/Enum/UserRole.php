<?php

namespace App\Entity\Enum;

use App\Api\Console\Authorization\Scope;

enum UserRole: string
{
    // this is shown to users as "Blog Admin" to avoid confusion with org-level admin role
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case WRITER = 'writer';
    case CONTRIBUTOR = 'contributor';

    /**
     * @return Scope[]
     */
    public function scopes(): array
    {
        return match ($this) {
            self::ADMIN => Scope::all(),
            self::EDITOR => [
                Scope::BLOG_READ,
                Scope::BLOG_WRITE,

                Scope::POSTS_READ,
                Scope::POSTS_WRITE,
                Scope::POSTS_PUBLISH_OWN,
                Scope::POSTS_PUBLISH_ALL,

                Scope::USERS_READ,
                Scope::USERS_ADD,
                Scope::USERS_WRITE,

                Scope::TAGS_READ,
                Scope::TAGS_WRITE,

                Scope::LANGUAGES_READ,
                Scope::LANGUAGES_WRITE,

                Scope::MEDIA_UPLOAD,
                Scope::MEDIA_MANAGE,

                Scope::NAVIGATIONS_READ,
                Scope::NAVIGATIONS_WRITE,

                Scope::REDIRECTS_READ,
                Scope::REDIRECTS_WRITE,

                Scope::THEMES_READ,

                Scope::LINK_ANALYSIS_MANAGE,
                Scope::AI_USE,
            ],
            self::WRITER => [
                Scope::BLOG_READ,

                Scope::POSTS_READ,
                Scope::POSTS_WRITE,
                Scope::POSTS_PUBLISH_OWN,

                Scope::USERS_READ,

                Scope::TAGS_READ,
                Scope::TAGS_WRITE,

                Scope::LANGUAGES_READ,

                Scope::MEDIA_UPLOAD,

                Scope::NAVIGATIONS_READ,

                Scope::REDIRECTS_READ,

                Scope::THEMES_READ,

                Scope::LINK_ANALYSIS_MANAGE,
                Scope::AI_USE,
            ],
            self::CONTRIBUTOR => [
                Scope::BLOG_READ,
                Scope::POSTS_READ,
                Scope::POSTS_WRITE,
                Scope::TAGS_READ,
                Scope::LANGUAGES_READ,
                Scope::MEDIA_UPLOAD,
            ],
        };
    }
}
