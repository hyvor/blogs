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

    public function test_update_from_subdomain_to_self(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            [
                'subdomain' => 'hosting-update-self',
                'hosting_at' => BlogHostingAt::SUBDOMAIN,
            ],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'self',
            'hosting_url' => 'https://myblog.com',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('subdomain', $json['hosting_at']);
        $this->assertIsArray($json['change']);
        $this->assertSame('changing', $json['change']['status']);
        $this->assertSame('subdomain', $json['change']['from_at']);
        $this->assertSame('self', $json['change']['to_at']);

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(HostingChangeMessage::class, $messages[0]);

        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findBy(['blog' => $blog])[0];

        $this->assertNotNull($hostingChange);
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $hostingChange->getFromAt());
        $this->assertSame('https://hosting-update-self.hyvorblogs.io', $hostingChange->getFromUrl());
        $this->assertSame(BlogHostingAt::SELF, $hostingChange->getToAt());
        $this->assertSame('https://myblog.com', $hostingChange->getToUrl());
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
        $this->assertNull($hostingChange->getFromDomain());
        $this->assertNull($hostingChange->getToDomain());
    }

    public function test_update_to_subdomain_from_self(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            [
                'subdomain' => 'hosting-update-subdomain',
                'hosting_at' => BlogHostingAt::SELF,
                'hosting_url' => 'https://old-self-hosted.com',
            ],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'subdomain',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        // the change is applied asynchronously, so the response still reflects the old state
        $json = $this->getJson();
        $this->assertSame('self', $json['hosting_at']);
        $this->assertIsArray($json['change']);
        $this->assertSame('changing', $json['change']['status']);
        $this->assertSame('self', $json['change']['from_at']);
        $this->assertSame('subdomain', $json['change']['to_at']);

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(HostingChangeMessage::class, $messages[0]);

        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findBy(['blog' => $blog])[0];
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
        $this->assertSame(BlogHostingAt::SELF, $hostingChange->getFromAt());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $hostingChange->getToAt());
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

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(HostingChangeMessage::class, $messages[0]);

        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findBy(['blog' => $blog])[0];
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $hostingChange->getToAt());
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

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(HostingChangeMessage::class, $messages[0]);

        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findBy(['blog' => $blog])[0];
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
        $this->assertSame(BlogHostingAt::SELF, $hostingChange->getToAt());
        $this->assertSame('https://myblog.com', $hostingChange->getToUrl());
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

    public function test_rejects_when_a_hosting_change_is_already_pending(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-update-pending', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        HostingChangeFactory::createOne(['blog' => $blog, 'status' => HostingChangeStatus::CHANGING]);

        $this->consoleBlogApi('POST', $blog, '/hosting', [
            'hosting_at' => 'self',
            'hosting_url' => 'https://example.com',
        ], user: $user);

        $this->assertResponseFailed(400, 'A hosting change is already in progress for this blog');
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

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(HostingChangeMessage::class, $messages[0]);

        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findBy(['blog' => $blog])[0];
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
        $this->assertSame('active.com', $hostingChange->getFromDomain());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $hostingChange->getToAt());
    }
}
