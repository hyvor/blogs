<?php

namespace App\Tests\Api\Console\Blog;

use App\Api\Console\Controller\BlogController;
use App\Api\Console\Object\BlogObject;
use App\Api\Console\Object\BlogObjectFactory;
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
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'subdomain' => 'blog-update-new',
            'hosting_at' => 'domain',
            'hosting_domain' => 'example.com',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('blog-update-new', $json['subdomain']);
        $this->assertSame('domain', $json['hosting_at']);
        $this->assertSame('example.com', $json['hosting_domain']);
    }

    public function test_updates_self_url_and_clears_custom_domain(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-self']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);

        $blog->setHostingDomain('hyvor.com');
        $this->getEm()->flush();

        $url = 'https://hyvor.com/blog';

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'hosting_at' => 'self',
            'hosting_url' => $url,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('self', $json['hosting_at']);
        $this->assertSame($url, $json['hosting_url']);
        $this->assertNull($json['hosting_domain']);
    }

    public function test_update_metadata(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-meta']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);

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
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'logo_url' => 'hello',
        ], user: $user);

        $this->assertResponseFailed(422, 'valid URL');
    }

    public function test_returns_error_when_custom_domain_is_taken(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-domain-taken']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);

        $blog->setHostingDomain('hyvor.com');
        $this->getEm()->flush();

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'hosting_domain' => 'hyvor.com',
        ], user: $user);

        $this->assertResponseFailed(422, 'domain_taken');
    }
}
