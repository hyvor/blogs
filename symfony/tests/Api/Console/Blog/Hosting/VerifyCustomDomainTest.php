<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\Enum\UserStatus;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\CustomDomainIntentFactory;
use App\Tests\Factory\HostingChangeFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainService::class)]
class VerifyCustomDomainTest extends ApiTestCase
{
    public function test_fails_when_no_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-verify-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain/verify', user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_when_only_an_active_custom_domain_exists(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-verify-active'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain/verify', user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_when_hosting_change_is_pending(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-verify-pending'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainIntentFactory::createFor($blog, 'pending.com');
        HostingChangeFactory::createOne(['blog' => $blog, 'status' => HostingChangeStatus::CHANGING]);

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain/verify', user: $user);

        $this->assertResponseFailed(400, 'A hosting change is already in progress for this blog');
    }
}
