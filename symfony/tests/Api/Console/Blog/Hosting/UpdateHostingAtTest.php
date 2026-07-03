<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\Enum\UserStatus;
use App\Entity\HostingChanges;
use App\Message\HostingChangeMessage;
use App\MessageHandler\HostingChangeMessageHandler;
use App\Service\Blog\Hosting\HostingChangeService;
use App\Service\Blog\Hosting\UpdateBlogUrlsService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\HostingChangeFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(HostingChangeService::class)]
#[CoversClass(UpdateBlogUrlsService::class)]
#[CoversClass(HostingChangeMessageHandler::class)]
#[CoversClass(HostingChangeMessage::class)]
class UpdateHostingAtTest extends ApiTestCase
{
    /**
     * Simulates the worker: asserts the async job was dispatched, then runs it
     * synchronously via its handler, as this codebase's tests do for other jobs.
     */
    private function processHostingChange(): HostingChanges
    {
        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(HostingChangeMessage::class, 1);

        /** @var HostingChangeMessage $message */
        $message = $dispatched->first(HostingChangeMessage::class)->getMessage();

        $this->getService(HostingChangeMessageHandler::class)($message);

        $hostingChange = $this->getEm()->find(HostingChanges::class, $message->hostingChangeId);
        $this->assertNotNull($hostingChange);

        return $hostingChange;
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
        $this->assertNotNull($json['change']);
        $this->assertSame('changing', $json['change']['status']);
        $this->assertSame('self', $json['change']['from_at']);
        $this->assertSame('subdomain', $json['change']['to_at']);

        $hostingChange = $this->processHostingChange();
        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());
        $this->assertSame(BlogHostingAt::SELF, $hostingChange->getFromAt());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $hostingChange->getToAt());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $blog->getHostingAt());
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

        $hostingChange = $this->processHostingChange();
        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $blog->getHostingAt());
        $this->assertNull($blog->getHostingUrl());
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

        $hostingChange = $this->processHostingChange();
        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertSame(BlogHostingAt::SELF, $blog->getHostingAt());
        $this->assertSame('https://myblog.com', $blog->getHostingUrl());
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

        $hostingChange = $this->processHostingChange();
        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());
        $this->assertSame('active.com', $hostingChange->getFromDomain());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $blog->getHostingAt());

        $this->assertNull(
            $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'active.com'])
        );
    }
}
