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
            self::EDITOR => Scope::allExcept([
                Scope::BLOG_DELETE,
                Scope::USERS_WRITE,
                Scope::ROUTES_WRITE,
                Scope::WEBHOOKS_READ,
                Scope::WEBHOOKS_WRITE,
                Scope::API_KEYS_READ,
                Scope::API_KEYS_WRITE,
                Scope::THEMES_READ,
                Scope::THEMES_WRITE,
                Scope::IMPORT_MANAGE
            ]),
            self::WRITER => [
                Scope::BLOG_READ,
                Scope::POSTS_READ,
                Scope::POSTS_WRITE,
                Scope::POSTS_PUBLISH_OWN,
                Scope::TAGS_READ,
                Scope::TAGS_WRITE,
                Scope::LANGUAGES_READ,
                Scope::MEDIA_UPLOAD,
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
