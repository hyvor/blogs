<?php

namespace App\Domains\Cache;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Models\Blog;
use Illuminate\Support\Facades\Cache;

/**
 * Caches delivery API response objects
 */
class CacheRepository
{

    private Blog $blog;

    public function blog(Blog $blog)
    {
        $this->blog = $blog;
        return $this;
    }

    private function getKey(string $path) : string
    {
        return "blog_cache_{$this->blog->id}_$path";
    }

    public function clearTemplateCache()
    {
        $key = $this->getKey(self::TEMPLATE_CACHE_CLEAR_KEY);
        Cache::put($key, now()->timestamp);
    }


    const TEMPLATE_CACHE_CLEAR_KEY =  'LAST_TEMPLATE_CACHE_CLEARED_AT';

    private static function getCacheKeyTag(Blog $blog): string
    {
        return "blog_cache_{$blog->id}";
    }

    private static function getCacheKey(Blog $blog, string $path): string
    {
        $tag = self::getCacheKeyTag($blog);

        return "blogs_cache_" . $tag . "_$path";
    }


    public static function set(
        Blog $blog,
        string $path,
        DeliveryAPIResponseObject $responseObject
    ): void
    {
        if (config('app.debug') === true) {
            return;
        }

        $tag = self::getCacheKeyTag($blog);
        $key = self::getCacheKey($blog, $path);
        Cache::tags($tag)->put($key, serialize($responseObject));
    }

    public static function get(Blog $blog, string $path): ?DeliveryAPIResponseObject
    {
        if (config('app.debug') === true) {
            return null;
        }

        $tag = self::getCacheKeyTag($blog);
        $key = self::getCacheKey($blog, $path);
        $cache = Cache::tags($tag)->get($key);

        $object = $cache ? unserialize($cache) : null;

        if (
            $object &&
            $object->type === DeliveryAPITypeEnum::FILE &&
            $object->is_template
        ) {

            $templateCacheClearedAt = (int) Cache::tags($tag)->get(self::TEMPLATE_CACHE_CLEAR_KEY);

            if ($templateCacheClearedAt && $object->at < $templateCacheClearedAt) {
                $object = null;
            }

        }

        return $object;

    }

    public static function clear(Blog $blog, string $path)
    {
        $tag = self::getCacheKeyTag($blog);
        $key = self::getCacheKey($blog, $path);
        Cache::tags($tag)->forget($key);
    }

    public static function clearAll(Blog $blog)
    {
        $tag = self::getCacheKeyTag($blog);
        Cache::tags($tag)->flush();
    }

}
