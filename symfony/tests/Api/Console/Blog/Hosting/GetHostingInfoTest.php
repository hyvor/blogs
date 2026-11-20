<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Api\Console\Object\CustomDomainIntentObject;
use App\Api\Console\Object\CustomDomainObject;
use App\Entity\Enum\UserStatus;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\CustomDomainIntentFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainObject::class)]
#[CoversClass(CustomDomainIntentObject::class)]
#[CoversClass(CustomDomainService::class)]
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
        $this->assertSame('https://hosting-get-no-domain.hyvorblogs.io', $json['subdomain_url']);
        $this->assertSame('https://hosting-get-no-domain.hyvorblogs.io', $json['url']);
        $this->assertNull($json['hosting_url']);
        $this->assertNull($json['custom_domain']);
        $this->assertNull($json['custom_domain_intent']);
        $this->assertArrayNotHasKey('hosting_at', $json);
        $this->assertArrayNotHasKey('change', $json);
    }

    public function test_returns_hosting_url(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-get-hosting-url', 'hosting_url' => 'https://example.com/blog'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('GET', $blog, '/hosting', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('https://example.com/blog', $json['hosting_url']);
        $this->assertSame('https://example.com/blog', $json['url']);
    }

    public function test_returns_hosting_info_with_pending_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-get-with-intent'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainIntentFactory::createFor($blog, 'mysite.com');

        $this->consoleBlogApi('GET', $blog, '/hosting', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['custom_domain']);
        $this->assertIsArray($json['custom_domain_intent']);
        $this->assertSame('mysite.com', $json['custom_domain_intent']['domain']);
    }

    public function test_returns_hosting_info_with_active_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-get-with-domain'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'mysite.com');

        $this->consoleBlogApi('GET', $blog, '/hosting', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('mysite.com', $json['custom_domain']['domain']);
        $this->assertSame('https://mysite.com', $json['url']);
        $this->assertSame('auto', $json['custom_domain']['tls_provider']);
        $this->assertNull($json['custom_domain_intent']);
    }
}
