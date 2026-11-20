<?php

namespace App\Tests\Api\Console\Blog;

use App\Api\Console\Controller\BlogController;
use App\Api\Console\Object\BlogObject;
use App\Api\Console\Object\BlogObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Service\Blog\BlogService;
use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogService::class)]
#[CoversClass(BlogObject::class)]
#[CoversClass(BlogObjectFactory::class)]
#[CoversClass(BlogUpdatedEvent::class)]
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

        $event = $this->getEd()->getFirstEvent(BlogUpdatedEvent::class);
        $this->assertSame($blog->getId(), $event->blog->getId());
        $this->assertSame('blog-update-new', $event->blog->getSubdomain());
        $this->assertSame('blog-update', $event->blogOld->getSubdomain());
    }

    public function test_updating_subdomain_changes_blog_url(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-url']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'subdomain' => 'blog-update-url-new',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('https://blog-update-url-new.hyvorblogs.io', $this->getJson()['url']);
    }

    public function test_keeping_the_same_subdomain_is_allowed(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-same']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'subdomain' => 'blog-update-same',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('blog-update-same', $this->getJson()['subdomain']);
    }

    public function test_fails_when_subdomain_is_taken(): void
    {
        BlogFactory::createOne(['subdomain' => 'blog-update-taken']);
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-taker']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'subdomain' => 'blog-update-taken',
        ], user: $user);

        $this->assertResponseFailed(422, 'subdomain is already taken');
        $this->getEm()->refresh($blog);
        $this->assertSame('blog-update-taker', $blog->getSubdomain());
    }

    public function test_fails_when_subdomain_is_reserved(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-reserved']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'subdomain' => 'billing',
        ], user: $user);

        $this->assertResponseFailed(422, 'subdomain is reserved');
    }

    public function test_fails_when_subdomain_is_invalid(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-invalid']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'subdomain' => '-invalid-',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
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

    public function test_updates_ai_settings(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-ai']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'ai_model' => 'claude-sonnet-5',
            'ai_translation_enabled' => true,
            'ai_agent' => true,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('claude-sonnet-5', $json['ai_model']);
        $this->assertTrue($json['ai_translation_enabled']);
        $this->assertTrue($json['ai_agent']);
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

    public function test_sets_hosting_url(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-hosting-url']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'hosting_url' => 'https://example.com/blog/',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        // trailing slash is removed
        $this->assertSame('https://example.com/blog', $json['hosting_url']);
        $this->assertSame('https://example.com/blog', $json['url']);
    }

    public function test_unsets_hosting_url(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-hosting-url', 'hosting_url' => 'https://example.com/blog']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'hosting_url' => '',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['hosting_url']);
        $this->assertSame('https://blog-update-hosting-url.hyvorblogs.io', $json['url']);
    }

    public function test_does_not_change_hosting_url_when_not_given(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-hosting-url-keep', 'hosting_url' => 'https://example.com/blog']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'logo_url' => 'https://example.com/image.png',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('https://example.com/blog', $this->getJson()['hosting_url']);
    }

    public function test_validates_hosting_url(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-update-hosting-url-invalid']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog', [
            'hosting_url' => 'example.com/blog',
        ], user: $user);

        $this->assertResponseFailed(422, 'valid URL');
    }
}
