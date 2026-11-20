<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\Enum\UserStatus;
use App\Entity\HostingChange;
use App\Service\Hosting\HostingChangeService;
use App\Service\Hosting\Message\HostingChangeMessage;
use App\Service\Hosting\MessageHandler\HostingChangeMessageHandler;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\HostingChangeFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(HostingChangeService::class)]
#[CoversClass(HostingChangeMessageHandler::class)]
#[CoversClass(HostingChangeMessage::class)]
class ChangeHostingAtTest extends ApiTestCase
{

    public function test_self_hosting_is_rejected(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-self', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'self',
            'hosting_url' => 'https://myblog.com',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
        $this->assertCount(0, $this->getEm()->getRepository(HostingChange::class)->findBy(['blog' => $blog]));
    }

    public function test_from_custom_domain_to_subdomain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            [
                'subdomain' => 'hosting-update-from-domain',
                'hosting_at' => BlogHostingAt::DOMAIN,
            ],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('domain', $json['hosting_at']);
        $this->assertIsArray($json['change']);
        $this->assertSame('changing', $json['change']['status']);
        $this->assertSame('domain', $json['change']['from_at']);
        $this->assertSame('subdomain', $json['change']['to_at']);

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(HostingChangeMessage::class, $messages[0]);

        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findBy(['blog' => $blog])[0];
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
        $this->assertSame(BlogHostingAt::DOMAIN, $hostingChange->getFromAt());
        $this->assertSame('active.com', $hostingChange->getFromDomain());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $hostingChange->getToAt());
        $this->assertSame('hosting-update-from-domain', $hostingChange->getToSubdomain());

        $this->assertNull($hostingChange->getFromSubdomain());
        $this->assertNull($hostingChange->getToDomain());
    }

    public function test_change_subdomain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-subdomain', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
            'subdomain' => 'new-subdomain',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('subdomain', $json['hosting_at']);
        $this->assertIsArray($json['change']);
        $this->assertSame('changing', $json['change']['status']);
        $this->assertSame('subdomain', $json['change']['from_at']);
        $this->assertSame('subdomain', $json['change']['to_at']);

        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findBy(['blog' => $blog])[0];
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $hostingChange->getFromAt());
        $this->assertSame('hosting-update-subdomain', $hostingChange->getFromSubdomain());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $hostingChange->getToAt());
        $this->assertSame('new-subdomain', $hostingChange->getToSubdomain());

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);
        $message = $messages[0];
        $this->assertInstanceOf(HostingChangeMessage::class, $message);
        $this->assertSame($hostingChange->getId(), $message->hostingChangeId);
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

    public function test_rejects_when_a_hosting_change_is_already_pending(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-pending', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        HostingChangeFactory::createOne(['blog' => $blog, 'status' => HostingChangeStatus::CHANGING]);

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
            'subdomain' => 'hosting-update-pending-new',
        ], user: $user);

        $this->assertResponseFailed(400, 'A hosting change is already in progress for this blog');
    }

    public function test_fails_when_changing_to_same_subdomain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-same', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
        ], user: $user);

        $this->assertResponseFailed(400, 'You are already hosting at subdomain: hosting-update-same');
    }

}
