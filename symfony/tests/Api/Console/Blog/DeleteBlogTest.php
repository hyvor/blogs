<?php

namespace App\Tests\Api\Console\Blog;

use App\Api\Console\Controller\BlogController;
use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Service\Blog\BlogService;
use App\Service\Blog\Event\BlogDeletedEvent;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogService::class)]
class DeleteBlogTest extends ApiTestCase
{
    public function test_soft_deletes_blog(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'delete-blog-owner']);
        $blogId = $blog->getId();

        $this->consoleBlogApi('DELETE', $blog, '/blog', user: $owner);

        $this->assertResponseIsSuccessful();
        $this->getEd()->assertDispatched(BlogDeletedEvent::class);

        $this->getEm()->clear();
        $deletedBlog = $this->getEm()->getRepository(Blog::class)->find($blogId);

        $this->assertNotNull($deletedBlog);
        $this->assertNotNull($deletedBlog->getDeletedAt());
    }

    public function test_requires_blog_delete_scope(): void
    {
        [$blog, ] = BlogFactory::createOneWithUser(['subdomain' => 'delete-blog-forbidden']);
        $editor = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);

        $this->consoleBlogApi('DELETE', $blog, '/blog', user: $editor);

        $this->assertResponseStatusCodeSame(403);
    }

}
