<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Api\Console\Object\CustomDomainObject;
use App\Entity\Enum\UserStatus;
use App\Service\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainObject::class)]
#[CoversClass(CustomDomainService::class)]
class UpdateCustomDomainTest extends ApiTestCase
{
    public function test_updates_pending_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createPendingFor($blog, 'old.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'old_domain' => 'old.com',
            'new_domain' => 'new.com',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('new.com', $json['domain']);
    }

    public function test_fails_when_domain_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'old_domain' => 'nonexistent.com',
            'new_domain' => 'new.com',
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_when_status_is_not_pending(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-active'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'old_domain' => 'active.com',
            'new_domain' => 'new.com',
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }
}
