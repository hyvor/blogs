<?php declare(strict_types=1);

namespace App\Domains\Cache;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Cache\Events\CacheClearAllEvent;
use App\Domains\Cache\Events\CacheClearSingleEvent;
use App\Domains\Cache\Events\CacheClearTemplatesEvent;
use App\Models\Blog;
use Illuminate\Support\Facades\Cache;

/**
 * Caches delivery API response objects
 */
class CacheService
{
    public const LAST_TEMPLATE_CACHE_CLEARED_AT = 'LAST_TEMPLATE_CACHE_CLEARED_AT';
    public const LAST_ALL_CACHE_CLEARED_AT = 'LAST_ALL_CACHE_CLEARED_AT';
    private Blog $blog;

    /*
     * This is not done in constructor because laravel mock has a problem
     * when the constructor has params
     */
    public function blog(Blog $blog): static
    {
        $this->blog = $blog;
        return $this;
    }

    private function getKey(string $path): string
    {
        return "blog_cache_{$this->blog->id}_$path";
    }

    public function clearTemplateCache() : void
    {
        $key = $this->getKey(self::LAST_TEMPLATE_CACHE_CLEARED_AT);
        Cache::put($key, now()->timestamp);

        CacheClearTemplatesEvent::dispatch($this->blog);
    }

    /**
     * @param string[] $paths
     */
    public function clearPathsCache(array $paths) : void
    {
        foreach ($paths as $path) {
            $this->clearSingleCache($path);
        }
    }

    public function clearSingleCache(string $path) : void
    {
        $key = $this->getKey($path);
        Cache::forget($key);

        CacheClearSingleEvent::dispatch($this->blog, $path);
    }

    public function clearAllCache() : void
    {
        $key = $this->getKey(self::LAST_ALL_CACHE_CLEARED_AT);
        Cache::put($key, now()->timestamp);

        CacheClearAllEvent::dispatch($this->blog);
    }

    public function set(string $path, DeliveryAPIResponseObject $responseObject) : void
    {
        $key = $this->getKey($path);
        Cache::put($key, serialize($responseObject));
    }

    public function get(string $path): ?DeliveryAPIResponseObject
    {
        $key = $this->getKey($path);
        $cache = Cache::get($key);

        if (!$cache) {
            return null;
        }

        if (!is_string($cache)) {
            return null;
        }

        $object = unserialize($cache);

        if (!($object instanceof DeliveryAPIResponseObject)) {
            return null;
        }

        $objectCreatedAt = $object->at;

        /**
         * Whole blog cache is cleared
         */
        $lastCacheAllCleared = Cache::get($this->getKey(self::LAST_ALL_CACHE_CLEARED_AT)) ?? 0;
        if ($objectCreatedAt < $lastCacheAllCleared) {
            return null;
        }

        if (
            $object->type === DeliveryAPITypeEnum::FILE &&
            $object->file_type === DeliveryAPIFileTypeEnum::TEMPLATE
        ) {
            $templateCacheClearedAt = Cache::get($this->getKey(self::LAST_TEMPLATE_CACHE_CLEARED_AT)) ?? 0;

            /**
             * Template cache is cleared
             */
            if ($objectCreatedAt < $templateCacheClearedAt) {
                return null;
            }
        }

        return $object;
    }
}
