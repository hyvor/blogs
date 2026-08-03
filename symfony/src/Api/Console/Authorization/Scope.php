<?php

namespace App\Api\Console\Authorization;

enum Scope: string
{
    case BLOG_READ = 'blog.read';
    case BLOG_WRITE = 'blog.write'; // change blog settings, hosting, etc
    case BLOG_DELETE = 'blog.delete';

    case POSTS_READ = 'posts.read';
    case POSTS_WRITE = 'posts.write';
    case POSTS_PUBLISH_OWN = 'posts.publish.own'; // publish own posts
    case POSTS_PUBLISH_ALL = 'posts.publish.all'; // publish all posts (editor)

    case USERS_READ = 'users.read';
    case USERS_ADD = 'users.add'; // ability to add new users
    case USERS_WRITE = 'users.write'; // ability to change current user's data and roles

    case TAGS_READ = 'tags.read';
    case TAGS_WRITE = 'tags.write';

    case LANGUAGES_READ = 'languages.read';
    case LANGUAGES_WRITE = 'languages.write';

    case MEDIA_UPLOAD = 'media.upload'; // upload media through posts
    case MEDIA_MANAGE = 'media.manage'; // manage media library (delete, rename, move)

    case NAVIGATIONS_READ = 'navigations.read';
    case NAVIGATIONS_WRITE = 'navigations.write';

    case ROUTES_READ = 'routes.read';
    case ROUTES_WRITE = 'routes.write';

    case REDIRECTS_READ = 'redirects.read';
    case REDIRECTS_WRITE = 'redirects.write';

    case WEBHOOKS_READ = 'webhooks.read';
    case WEBHOOKS_WRITE = 'webhooks.write';

    case API_KEYS_READ = 'api_keys.read';
    case API_KEYS_WRITE = 'api_keys.write';

    case THEMES_READ = 'themes.read';
    case THEMES_WRITE = 'themes.write';

    case IMPORT_MANAGE = 'import.manage';
    case EXPORT_MANAGE = 'export.manage';

    case LINK_ANALYSIS_MANAGE = 'link_analysis.manage';
    case INTEGRATIONS_MANAGE = 'integrations.manage';
    case AI_MANAGE = 'ai.use';

    /**
     * @return self[]
     */
    public static function all(): array
    {
        return Scope::cases();
    }

    /**
     * @param Scope[] $except
     * @return Scope[]
     */
    public static function allExcept(array $except): array
    {
        return array_filter(Scope::cases(), fn(Scope $scope) => !in_array($scope, $except, true));
    }
}
