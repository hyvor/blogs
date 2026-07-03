<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Api\Console\Object\CustomDomainObject;
use App\Entity\CustomDomain;
use App\Entity\Enum\CustomDomainStatus;
use App\Entity\Enum\UserStatus;
use App\Service\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainObject::class)]
#[CoversClass(CustomDomainService::class)]
class CreateCustomDomainTest extends ApiTestCase
{
    public function test_creates_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'mysite.com',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('mysite.com', $json['domain']);
        $this->assertSame('pending', $json['status']);

        $domain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'mysite.com']);
        $this->assertNotNull($domain);
        $this->assertSame(CustomDomainStatus::PENDING, $domain->getStatus());
    }

    public function test_fails_when_domain_already_exists(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-dup'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createPendingFor($blog, 'taken.com');

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'taken.com',
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }
}
