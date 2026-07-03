<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainStatus;
use App\Entity\Enum\UserStatus;
use App\Service\Blog\BlogService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(BlogService::class)]
class UpdateHostingAtTest extends ApiTestCase
{
    public function test_update_to_subdomain_from_self(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-subdomain', 'hosting_at' => BlogHostingAt::SELF],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('subdomain', $json['hosting_at']);
    }

    public function test_update_to_subdomain_clears_hosting_url(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            [
                'subdomain' => 'hosting-update-subdomain-clears-url',
                'hosting_at' => BlogHostingAt::SELF,
                'hosting_url' => 'https://old-self-hosted.com',
            ],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('subdomain', $json['hosting_at']);
        $this->assertNull($json['hosting_url']);

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNull($blog->getHostingUrl());
    }

    public function test_update_to_self_requires_url(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-self-no-url', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'self',
        ], user: $user);

        $this->assertResponseFailed(400, 'Hosting URL is required when self-hosting');
    }

    public function test_update_to_self_with_url(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-self', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'self',
            'hosting_url' => 'https://myblog.com',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('self', $json['hosting_at']);
        $this->assertSame('https://myblog.com', $json['hosting_url']);
    }

    public function test_update_to_domain_is_rejected(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-to-domain', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'domain',
        ], user: $user);

        $this->assertResponseFailed(400, 'Use custom domain endpoints to set hosting at domain');
    }

    public function test_update_to_same_hosting_at_is_rejected(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-same-value', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
        ], user: $user);

        $this->assertResponseFailed(400, 'Hosting at is already set to the requested value: subdomain');
    }

    public function test_update_from_domain_to_subdomain_deletes_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-from-domain', 'hosting_at' => BlogHostingAt::DOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $customDomain = CustomDomainFactory::createActiveFor($blog, 'active.com');
        $blog->setCustomDomain($customDomain);
        $this->getEm()->flush();

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('subdomain', $json['hosting_at']);

        $this->assertNull(
            $this->getEm()->getRepository(\App\Entity\CustomDomain::class)->findOneBy(['domain' => 'active.com'])
        );
    }
}
