<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Api\Console\Object\CustomDomainObject;
use App\Api\Console\Object\HostingChangeObject;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\UserStatus;
use App\Service\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\HostingChangeFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainObject::class)]
#[CoversClass(CustomDomainService::class)]
#[CoversClass(HostingChangeObject::class)]
class GetHostingInfoTest extends ApiTestCase
{
    public function test_returns_hosting_info_without_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-get-no-domain'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('GET', $blog, '/hosting', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('hosting_at', $json);
        $this->assertNull($json['custom_domain']);
        $this->assertNull($json['change']);
    }

    public function test_returns_ongoing_change(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-get-with-change', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SUBDOMAIN,
            'to_at' => BlogHostingAt::SELF,
            'to_url' => 'https://example.com',
        ]);

        $this->consoleBlogApi('GET', $blog, '/hosting', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['change']);
        $this->assertSame('changing', $json['change']['status']);
        $this->assertSame('subdomain', $json['change']['from_at']);
        $this->assertSame('self', $json['change']['to_at']);
        $this->assertSame('https://example.com', $json['change']['to_url']);
    }

    public function test_returns_hosting_info_with_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-get-with-domain'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createPendingFor($blog, 'mysite.com');

        $this->consoleBlogApi('GET', $blog, '/hosting', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('mysite.com', $json['custom_domain']['domain']);
        $this->assertSame('pending', $json['custom_domain']['status']);
    }
}
