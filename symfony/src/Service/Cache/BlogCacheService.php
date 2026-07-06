<?php

namespace App\Service\Cache;

use App\Entity\Blog;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BlogCacheService
{
    public const LAST_TEMPLATE_CACHE_CLEARED_AT = 'LAST_TEMPLATE_CACHE_CLEARED_AT';
    public const LAST_ALL_CACHE_CLEARED_AT = 'LAST_ALL_CACHE_CLEARED_AT';

    public function __construct(
        private Connection $connection,
        private EventDispatcherInterface $dispatcher,
        private string $cachePrefix = '',
    ) {}

    private function getKey(Blog $blog, string $path): string
    {
        return $this->cachePrefix . "blog_cache_{$blog->getId()}_$path";
    }

    public function clearTemplateCache(Blog $blog): void
    {
        $this->put($this->getKey($blog, self::LAST_TEMPLATE_CACHE_CLEARED_AT), time());
        $this->dispatcher->dispatch(new CacheClearTemplatesEvent($blog));
    }

    public function clearAllCache(Blog $blog): void
    {
        $this->put($this->getKey($blog, self::LAST_ALL_CACHE_CLEARED_AT), time());
        $this->dispatcher->dispatch(new CacheClearAllEvent($blog));
    }

    public function clearSingleCache(Blog $blog, string $path): void
    {
        $this->forget($this->getKey($blog, $path));
        $this->dispatcher->dispatch(new CacheClearSingleEvent($blog, $path));
    }

    /** @param string[] $paths */
    public function clearPathsCache(Blog $blog, array $paths): void
    {
        foreach ($paths as $path) {
            $this->clearSingleCache($blog, $path);
        }
    }

    private function put(string $key, mixed $value): void
    {
        // Matches Laravel database cache driver format: base64_encode(serialize($value))
        $serialized = base64_encode(serialize($value));

        // Max 32-bit signed int — far-future "forever" matching the cache table integer column
        $forever = 2147483647;

        $this->connection->executeStatement(
            'INSERT INTO cache (key, value, expiration) VALUES (:key, :value, :expiration)
             ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value, expiration = EXCLUDED.expiration',
            ['key' => $key, 'value' => $serialized, 'expiration' => $forever],
        );
    }

    private function forget(string $key): void
    {
        $this->connection->executeStatement('DELETE FROM cache WHERE key = :key', ['key' => $key]);
    }
}
