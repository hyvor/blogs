<?php

namespace App\Domains\Cache;

use App\Models\Blog;

/**
 * Handles different cache versions used for cache invalidation.
 */
class CacheVersion
{

    public static function updateStyleVersion(Blog $blog): void
    {
        $blog->setMeta('cache_version_styles', $blog->getMeta('cache_version_styles') + 1);
    }

}