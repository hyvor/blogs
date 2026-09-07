<?php

namespace App\Entity\Enum;

enum WebhookEvent: string
{
    case BLOG_UPDATED = 'blog.updated';

    case POST_CREATED = 'post.created';
    case POST_UPDATED = 'post.updated';
    case POST_DELETED = 'post.deleted';

    case POST_VARIANT_PUBLISHED = 'post.variant.published';
    case POST_VARIANT_UNPUBLISHED = 'post.variant.unpublished';

    case TAG_CREATED = 'tag.created';
    case TAG_UPDATED = 'tag.updated';
    case TAG_DELETED = 'tag.deleted';

    case USER_CREATED = 'user.created';
    case USER_UPDATED = 'user.updated';
    case USER_DELETED = 'user.deleted';

    case MEDIA_CREATED = 'media.created';
    case MEDIA_DELETED = 'media.deleted';

    case NAVIGATION_CHANGED = 'navigation.changed';
    case ROUTES_CHANGED = 'routes.changed';
    case LANGUAGES_CHANGED = 'languages.changed';

    case CACHE_SINGLE = 'cache.single';
    case CACHE_TEMPLATES = 'cache.templates';
    case CACHE_ALL = 'cache.all';
}
