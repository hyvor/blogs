<?php

namespace App\Service\Cache;

use App\Entity\Blog;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\Dto\DeliveryResponseType;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BlogCacheService
{
    public const LAST_TEMPLATE_CACHE_CLEARED_AT = 'LAST_TEMPLATE_CACHE_CLEARED_AT';
    public const LAST_ALL_CACHE_CLEARED_AT = 'LAST_ALL_CACHE_CLEARED_AT';

    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private CacheItemPoolInterface $cache,
    ) {}

    /**
     * key is hashed via xxh3 since path may contain special characters not allowed in cache keys
     * xxH3 is fast and has a low collision rate than md5 https://php.watch/versions/8.1/xxHash
     */
    private function getKey(Blog $blog, string $path): string
    {
        return hash('xxh3', "blog_cache_{$blog->getId()}_$path");
    }

    public function clearTemplateCache(Blog $blog): void
    {
        $this->saveCacheItem($this->getKey($blog, self::LAST_TEMPLATE_CACHE_CLEARED_AT), time());
        $this->dispatcher->dispatch(new CacheClearTemplatesEvent($blog));
    }

    public function clearAllCache(Blog $blog): void
    {
        $this->saveCacheItem($this->getKey($blog, self::LAST_ALL_CACHE_CLEARED_AT), time());
        $this->dispatcher->dispatch(new CacheClearAllEvent($blog));
    }

    public function clearSingleCache(Blog $blog, string $path): void
    {
        $this->deleteCacheItem($this->getKey($blog, $path));
        $this->dispatcher->dispatch(new CacheClearSingleEvent($blog, $path));
    }

    /** @param string[] $paths */
    public function clearPathsCache(Blog $blog, array $paths): void
    {
        foreach ($paths as $path) {
            $this->clearSingleCache($blog, $path);
        }
    }

    private function saveCacheItem(
        string $key,
        int|DeliveryResponse $value,
        ?int $ttl = 30 * 24 * 60 * 60 // null means no expiration, default is 30 days
    ): void
    {
        $item = $this->cache->getItem($key);
        if ($ttl !== null) {
            $item->expiresAfter($ttl);
        }
        $item->set(is_int($value) ? $value : serialize($value));
        $this->cache->save($item);
    }

    private function deleteCacheItem(string $key): void
    {
        $this->cache->deleteItem($key);
    }

    private function getIntItem(string $key): int
    {
        $item = $this->cache->getItem($key);
        $value = $item->get();
        if (!is_int($value)) {
            return 0;
        }
        return $value;
    }

    public function setResponse(Blog $blog, string $path, DeliveryResponse $responseObject) : void
    {
        $key = $this->getKey($blog, $path);
        $this->saveCacheItem($key, $responseObject);
    }

    public function getResponse(Blog $blog, string $path): ?DeliveryResponse
    {
        $key = $this->getKey($blog, $path);
        $cache = $this->cache->getItem($key)->get();

        if (!$cache) {
            return null;
        }

        if (!is_string($cache)) {
            return null;
        }

        $object = unserialize($cache);

        if (!($object instanceof DeliveryResponse)) {
            return null;
        }

        $objectCreatedAt = $object->at;

        // when the whole blog cache is cleared
        $lastCacheAllCleared = $this->getIntItem($this->getKey($blog, self::LAST_ALL_CACHE_CLEARED_AT));
        if (is_int($lastCacheAllCleared) && $objectCreatedAt < $lastCacheAllCleared) {
            return null;
        }

        if (
            $object->type === DeliveryResponseType::FILE &&
            $object->fileType === DeliveryFileType::TEMPLATE
        ) {
            // if template cache is cleared
            $templateCacheClearedAt = $this->getIntItem($this->getKey($blog, self::LAST_TEMPLATE_CACHE_CLEARED_AT));
            if ($objectCreatedAt < $templateCacheClearedAt) {
                return null;
            }
        }

        return $object;
    }
}
