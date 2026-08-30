<?php

namespace App\Tests\Api\Console\Blog;

use App\Api\Console\Controller\BlogController;
use App\Entity\Enum\UserRole;
use App\Service\Cache\BlogCacheService;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Cache\Event\CacheClearSingleEvent;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogCacheService::class)]
class DeleteBlogCacheTest extends ApiTestCase
{
    public function test_clears_all_cache(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'cache-clear-all']);

        $this->consoleBlogApi('DELETE', $blog, '/blog/cache', ['type' => 'all'], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->getEd()->assertDispatched(CacheClearAllEvent::class);
    }

    public function test_clears_template_cache(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'cache-clear-template']);

        $this->consoleBlogApi('DELETE', $blog, '/blog/cache', ['type' => 'template'], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->getEd()->assertDispatched(CacheClearTemplatesEvent::class);
    }

    public function test_clears_paths_cache(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'cache-clear-paths']);

        $cache = $this->getService(CacheItemPoolInterface::class);
        $cacheKey = hash('xxh3', "blog_cache_{$blog->getId()}_/about");
        $item = $cache->getItem($cacheKey);
        $item->set('test-value');
        $cache->save($item);

        $this->consoleBlogApi('DELETE', $blog, '/blog/cache', [
            'type' => 'paths',
            'paths' => ['/about', '/contact'],
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->getEd()->assertDispatchedCount(CacheClearSingleEvent::class, 2);

        $this->assertFalse($cache->getItem($cacheKey)->isHit());
    }

    public function test_requires_blog_delete_scope(): void
    {
        [$blog, ] = BlogFactory::createOneWithUser(['subdomain' => 'cache-clear-forbidden']);
        $editor = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);

        $this->consoleBlogApi('DELETE', $blog, '/blog/cache', ['type' => 'all'], user: $editor);

        $this->assertResponseStatusCodeSame(403);
    }
}
