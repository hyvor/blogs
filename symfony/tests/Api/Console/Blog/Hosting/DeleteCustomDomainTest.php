<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\CustomDomainIntent;
use App\Entity\Enum\UserStatus;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\CustomDomainIntentFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainService::class)]
class DeleteCustomDomainTest extends ApiTestCase
{
    public function test_deletes_pending_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainIntentFactory::createFor($blog, 'delete-me.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseIsSuccessful();

        $intent = $this->getEm()->getRepository(CustomDomainIntent::class)->findOneBy(['domain' => 'delete-me.com']);
        $this->assertNull($intent);
    }

    public function test_fails_when_no_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_when_only_an_active_custom_domain_exists(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete-active'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseFailed(400, 'There is no pending custom domain setup to abort. Switch to subdomain hosting instead to remove an active custom domain.');
    }
}
