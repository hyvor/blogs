<?php

namespace App\Tests\Service\Cache;

use App\Service\Cache\BlogCacheService;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Tests\Factory\BlogFactory;
use Doctrine\DBAL\Connection;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogCacheService::class)]
class BlogCacheServiceTest extends KernelTestCase
{
    private function service(): BlogCacheService
    {
        return $this->getService(BlogCacheService::class);
    }

    private function cacheRow(string $key): mixed
    {
        /** @var Connection $conn */
        $conn = $this->getService(Connection::class);
        return $conn->fetchOne('SELECT value FROM cache WHERE key = ?', [$key]);
    }

    public function test_clear_template_cache_writes_timestamp_and_dispatches_event(): void
    {
        $blog = BlogFactory::createOne();

        $this->service()->clearTemplateCache($blog);

        $key = "blog_cache_{$blog->getId()}_" . BlogCacheService::LAST_TEMPLATE_CACHE_CLEARED_AT;
        $this->assertNotFalse($this->cacheRow($key));
        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_clear_all_cache_writes_timestamp_and_dispatches_event(): void
    {
        $blog = BlogFactory::createOne();

        $this->service()->clearAllCache($blog);

        $key = "blog_cache_{$blog->getId()}_" . BlogCacheService::LAST_ALL_CACHE_CLEARED_AT;
        $this->assertNotFalse($this->cacheRow($key));
        $this->getEd()->assertDispatched(CacheClearAllEvent::class);
    }

    public function test_clear_single_cache_removes_key_and_dispatches_event(): void
    {
        $blog = BlogFactory::createOne();

        /** @var Connection $conn */
        $conn = $this->getService(Connection::class);
        $key = "blog_cache_{$blog->getId()}_/test-path";
        $conn->executeStatement(
            'INSERT INTO cache (key, value, expiration) VALUES (?, ?, ?)',
            [$key, base64_encode(serialize('cached')), 2147483647],
        );

        $this->service()->clearSingleCache($blog, '/test-path');

        $this->assertFalse($this->cacheRow($key));
        $this->getEd()->assertDispatched(CacheClearSingleEvent::class);
    }

    public function test_clear_single_cache_is_noop_when_key_does_not_exist(): void
    {
        $blog = BlogFactory::createOne();

        $this->service()->clearSingleCache($blog, '/nonexistent');

        $this->getEd()->assertDispatched(CacheClearSingleEvent::class);
    }
}
