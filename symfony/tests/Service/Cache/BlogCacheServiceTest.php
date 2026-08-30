<?php

namespace App\Tests\Service\Cache;

use App\Service\Cache\BlogCacheService;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(BlogCacheService::class)]
class BlogCacheServiceTest extends KernelTestCase
{
    private function service(): BlogCacheService
    {
        return $this->getService(BlogCacheService::class);
    }

    private function cache(): CacheItemPoolInterface
    {
        return $this->getService(CacheItemPoolInterface::class);
    }

    private function cacheKey(int $blogId, string $key): string
    {
        return hash('xxh3', "blog_cache_{$blogId}_$key");
    }

    public function test_clear_template_cache_writes_timestamp_and_dispatches_event(): void
    {
        $blog = BlogFactory::createOne();

        $this->service()->clearTemplateCache($blog);

        $key = $this->cacheKey($blog->getId(), BlogCacheService::LAST_TEMPLATE_CACHE_CLEARED_AT);
        $this->assertTrue($this->cache()->getItem($key)->isHit());
        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_clear_all_cache_writes_timestamp_and_dispatches_event(): void
    {
        $blog = BlogFactory::createOne();

        $this->service()->clearAllCache($blog);

        $key = $this->cacheKey($blog->getId(), BlogCacheService::LAST_ALL_CACHE_CLEARED_AT);
        $this->assertTrue($this->cache()->getItem($key)->isHit());
        $this->getEd()->assertDispatched(CacheClearAllEvent::class);
    }

    public function test_clear_single_cache_removes_key_and_dispatches_event(): void
    {
        $blog = BlogFactory::createOne();

        $key = $this->cacheKey($blog->getId(), '/test-path');
        $item = $this->cache()->getItem($key);
        $item->set('cached');
        $this->cache()->save($item);

        $this->service()->clearSingleCache($blog, '/test-path');

        $this->assertFalse($this->cache()->getItem($key)->isHit());
        $this->getEd()->assertDispatched(CacheClearSingleEvent::class);
    }

    public function test_clear_single_cache_is_noop_when_key_does_not_exist(): void
    {
        $blog = BlogFactory::createOne();

        $this->service()->clearSingleCache($blog, '/nonexistent');

        $this->getEd()->assertDispatched(CacheClearSingleEvent::class);
    }
}
