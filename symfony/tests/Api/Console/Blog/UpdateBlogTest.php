<?php

namespace App\Tests\Api\Console\Blog;

use App\Api\Console\Controller\BlogController;
use App\Api\Console\Object\BlogObject;
use App\Api\Console\Object\BlogObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Service\Blog\BlogService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogService::class)]
#[CoversClass(BlogObject::class)]
#[CoversClass(BlogObjectFactory::class)]
class UpdateBlogTest extends ApiTestCase
{
    public function test_updates_blog_data(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'subdomain' => 'blog-update-new',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('blog-update-new', $json['subdomain']);
    }

    public function test_update_metadata(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-meta']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $logo = 'https://example.com/image.png';
        $cover = 'https://example.com/cover.png';

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'logo_url' => $logo,
            'cover_url' => $cover,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($logo, $json['logo_url']);
        $this->assertSame($cover, $json['cover_url']);
    }

    public function test_validates_urls(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-validate']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'logo_url' => 'hello',
        ], user: $user);

        $this->assertResponseFailed(422, 'valid URL');
    }
}
