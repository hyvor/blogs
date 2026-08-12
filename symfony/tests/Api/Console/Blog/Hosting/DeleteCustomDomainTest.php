<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\CustomDomain;
use App\Entity\Enum\UserStatus;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainService::class)]
class DeleteCustomDomainTest extends ApiTestCase
{
    public function test_deletes_pending_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createPendingFor($blog, 'delete-me.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseIsSuccessful();

        $domain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'delete-me.com']);
        $this->assertNull($domain);
    }

    public function test_fails_when_no_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseFailed(400, 'Custom domain does not exist');
    }

    public function test_fails_when_status_is_not_pending(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete-active'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseFailed(400, 'Only custom domains with PENDING status can be deleted');
    }
}
