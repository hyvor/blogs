<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\CustomDomain;
use App\Entity\CustomDomainIntent;
use App\Entity\Enum\UserStatus;
use App\Service\Blog\Event\BlogHostingChangedEvent;
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
    // DELETE /hosting/custom-domain ====

    public function test_removes_active_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-remove'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'remove-me.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['custom_domain']);
        $this->assertSame('https://hosting-cd-remove.hyvorblogs.io', $json['subdomain_url']);

        $this->assertNull($this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'remove-me.com']));

        $this->getEm()->refresh($blog);
        $this->assertNull($blog->getCustomDomain());

        $this->getEd()->assertDispatched(BlogHostingChangedEvent::class);
    }

    public function test_removing_active_custom_domain_keeps_pending_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-remove-keep-intent'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');
        CustomDomainIntentFactory::createFor($blog, 'next.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['custom_domain']);
        $this->assertIsArray($json['custom_domain_intent']);
        $this->assertSame('next.com', $json['custom_domain_intent']['domain']);
    }

    public function test_fails_removing_when_no_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-remove-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain', user: $user);

        $this->assertResponseFailed(400, 'There is no custom domain connected to this blog');
    }

    // DELETE /hosting/custom-domain/intent ====

    public function test_deletes_pending_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainIntentFactory::createFor($blog, 'delete-me.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain/intent', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['custom_domain_intent']);

        $intent = $this->getEm()->getRepository(CustomDomainIntent::class)->findOneBy(['domain' => 'delete-me.com']);
        $this->assertNull($intent);

        $this->getEd()->assertNotDispatched(BlogHostingChangedEvent::class);
    }

    public function test_deleting_intent_keeps_active_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete-keep-active'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');
        CustomDomainIntentFactory::createFor($blog, 'next.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain/intent', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('active.com', $json['custom_domain']['domain']);
        $this->assertNull($json['custom_domain_intent']);
    }

    public function test_fails_deleting_intent_when_no_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-delete-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');

        $this->consoleBlogApi('DELETE', $blog, '/hosting/custom-domain/intent', user: $user);

        $this->assertResponseFailed(400, 'There is no pending custom domain setup to abort');
    }
}
