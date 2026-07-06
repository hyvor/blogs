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
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;

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

        $connection = $this->getService(Connection::class);
        $connection->executeStatement(
            "INSERT INTO cache (key, value, expiration) VALUES (?, ?, ?)",
            ["blog_cache_{$blog->getId()}_/about", 'test-value', 2147483647],
        );

        $this->consoleBlogApi('DELETE', $blog, '/blog/cache', [
            'type' => 'paths',
            'paths' => ['/about', '/contact'],
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->getEd()->assertDispatchedCount(CacheClearSingleEvent::class, 2);

        /** @var int|string|false $remaining */
        $remaining = $connection->fetchOne(
            'SELECT COUNT(*) FROM cache WHERE key = ?',
            ["blog_cache_{$blog->getId()}_/about"],
        );
        $this->assertSame(0, (int) $remaining);
    }

    public function test_requires_blog_delete_scope(): void
    {
        [$blog, ] = BlogFactory::createOneWithUser(['subdomain' => 'cache-clear-forbidden']);
        $editor = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);

        $this->consoleBlogApi('DELETE', $blog, '/blog/cache', ['type' => 'all'], user: $editor);

        $this->assertResponseStatusCodeSame(403);
    }
}
