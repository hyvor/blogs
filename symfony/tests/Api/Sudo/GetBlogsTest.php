<?php

namespace Api\Sudo;

use App\Api\Sudo\Controller\BlogController;
use App\Api\Sudo\Input\BlogListInput;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogListInput::class)]
class GetBlogsTest extends ApiTestCase
{

    public function test_requires_sudo_access(): void
    {
        $this->sudoApi('GET', '/blogs');

        $this->assertResponseFailed(403, 'auth_required');
    }

    public function test_lists_blogs_with_organization_info(): void
    {
        $blog1 = BlogFactory::createOne([
            'organization_id' => 1001,
            'subdomain' => 'sudo-blog-one',
            'hyvor_user_id' => 2001,
        ]);
        $blog2 = BlogFactory::createOne([
            'organization_id' => 1002,
            'subdomain' => 'sudo-blog-two',
            'hyvor_user_id' => 2002,
        ]);

        $this->sudoApi('GET', '/blogs', user: 123);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['blogs']);
        $this->assertCount(2, $json['blogs']);

        $ids = [];
        foreach ($json['blogs'] as $blog) {
            $this->assertIsArray($blog);
            $ids[] = $blog['id'];
        }
        $this->assertContains($blog1->getId(), $ids);
        $this->assertContains($blog2->getId(), $ids);

        $firstBlog = $json['blogs'][0];
        $this->assertIsArray($firstBlog);
        $this->assertArrayHasKey('variants', $firstBlog);

        $this->assertIsArray($json['orgs']);
        $orgIds = [];
        foreach ($json['orgs'] as $org) {
            $this->assertIsArray($org);
            $orgIds[] = $org['id'];
        }
        $this->assertContains(1001, $orgIds);
        $this->assertContains(1002, $orgIds);

        $firstOrg = $json['orgs'][0];
        $this->assertIsArray($firstOrg);
        $this->assertArrayHasKey('name', $firstOrg);
        $this->assertArrayHasKey('billing_email', $firstOrg);
        $this->assertArrayHasKey('billing_address', $firstOrg);
    }

    public function test_filters_blogs_by_blog_id(): void
    {
        $blog1 = BlogFactory::createOne([
            'organization_id' => 1001,
            'subdomain' => 'sudo-filter-one',
            'hyvor_user_id' => 2001,
        ]);
        BlogFactory::createOne([
            'organization_id' => 1002,
            'subdomain' => 'sudo-filter-two',
            'hyvor_user_id' => 2002,
        ]);

        $this->sudoApi('GET', '/blogs?blog_id=' . $blog1->getId(), user: 123);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['blogs']);
        $this->assertCount(1, $json['blogs']);
        $firstBlog = $json['blogs'][0];
        $this->assertIsArray($firstBlog);
        $this->assertSame($blog1->getId(), $firstBlog['id']);
    }

    public function test_filters_blogs_by_subdomain(): void
    {
        BlogFactory::createOne([
            'organization_id' => 1001,
            'subdomain' => 'sudo-filter-one',
            'hyvor_user_id' => 2001,
        ]);
        BlogFactory::createOne([
            'organization_id' => 1002,
            'subdomain' => 'sudo-filter-two',
            'hyvor_user_id' => 2002,
        ]);

        $this->sudoApi('GET', '/blogs?subdomain=sudo-filter-two', user: 123);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['blogs']);
        $this->assertCount(1, $json['blogs']);
        $firstBlog = $json['blogs'][0];
        $this->assertIsArray($firstBlog);
        $this->assertSame('sudo-filter-two', $firstBlog['subdomain']);
    }

    public function test_filters_blogs_by_user_id(): void
    {
        $blog1 = BlogFactory::createOne([
            'organization_id' => 1001,
            'subdomain' => 'sudo-filter-one',
            'hyvor_user_id' => 2001,
        ]);
        BlogFactory::createOne([
            'organization_id' => 1002,
            'subdomain' => 'sudo-filter-two',
            'hyvor_user_id' => 2002,
        ]);

        $this->sudoApi('GET', '/blogs?user_id=2001', user: 123);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['blogs']);
        $this->assertCount(1, $json['blogs']);
        $firstBlog = $json['blogs'][0];
        $this->assertIsArray($firstBlog);
        $this->assertSame($blog1->getId(), $firstBlog['id']);
    }

    public function test_get_blog_by_id(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'sudo-single-blog']);

        $this->sudoApi('GET', '/blogs/' . $blog->getId(), user: 123);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertSame($blog->getId(), $json['id']);
        $this->assertSame('sudo-single-blog', $json['subdomain']);
        $this->assertArrayHasKey('variants', $json);
        $this->assertIsArray($json['variants']);
    }

    public function test_get_blog_not_found(): void
    {
        $this->sudoApi('GET', '/blogs/999999999', user: 123);

        $this->assertResponseFailed(404);
    }

}
