<?php

namespace App\Tests\MessageHandler;

use App\Entity\Blog;
use App\Entity\Post;
use App\Service\Blog\Message\HardDeleteBlogsMessage;
use App\Service\Blog\MessageHandler\HardDeleteBlogsMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\PostFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HardDeleteBlogsMessageHandler::class)]
class HardDeleteBlogsMessageHandlerTest extends KernelTestCase
{
    public function test_hard_deletes_blogs_soft_deleted_over_30_days_ago(): void
    {
        $oldDeletedBlog = BlogFactory::createOne(['deleted_at' => new \DateTimeImmutable('-31 days')]);
        $oldDeletedBlogId = $oldDeletedBlog->getId();
        $post = PostFactory::createOne(['blog' => $oldDeletedBlog]);
        $postId = $post->getId();

        $recentlyDeletedBlog = BlogFactory::createOne(['deleted_at' => new \DateTimeImmutable('-1 day')]);
        $recentlyDeletedBlogId = $recentlyDeletedBlog->getId();

        $notDeletedBlog = BlogFactory::createOne(['deleted_at' => null]);
        $notDeletedBlogId = $notDeletedBlog->getId();

        $handler = $this->getService(HardDeleteBlogsMessageHandler::class);
        $handler(new HardDeleteBlogsMessage());

        $this->getEm()->clear();

        $this->assertNull($this->getEm()->getRepository(Blog::class)->find($oldDeletedBlogId));
        $this->assertNull($this->getEm()->getRepository(Post::class)->find($postId));

        $this->assertNotNull($this->getEm()->getRepository(Blog::class)->find($recentlyDeletedBlogId));
        $this->assertNotNull($this->getEm()->getRepository(Blog::class)->find($notDeletedBlogId));
    }
}
