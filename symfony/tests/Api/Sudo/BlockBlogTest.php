<?php

namespace Api\Sudo;

use App\Api\Sudo\Controller\BlogController;
use App\Entity\Blog;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
class BlockBlogTest extends ApiTestCase
{

    public function test_requires_sudo_access(): void
    {
        $blog = BlogFactory::createOne();
        $this->sudoApi('POST', '/blogs/' . $blog->getId() . '/block');
        $this->assertResponseFailed(403, 'auth_required');
    }

    public function test_blocks_blog(): void
    {
        $blog = BlogFactory::createOne();

        $this->sudoApi('POST', '/blogs/' . $blog->getId() . '/block', user: 123);
        $this->assertResponseIsSuccessful();

        $this->getEm()->clear();
        $fresh = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($fresh);
        $this->assertNotNull($fresh->getBlockedAt());
    }

    public function test_unblocks_blog(): void
    {
        $blog = BlogFactory::createOne(['blocked_at' => new \DateTimeImmutable()]);

        $this->sudoApi('POST', '/blogs/' . $blog->getId() . '/unblock', user: 123);
        $this->assertResponseIsSuccessful();

        $this->getEm()->clear();
        $fresh = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($fresh);
        $this->assertNull($fresh->getBlockedAt());
    }
}
