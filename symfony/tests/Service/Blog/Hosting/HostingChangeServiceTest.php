<?php

namespace App\Tests\Service\Blog\Hosting;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Service\Blog\Event\BlogHostingChangedEvent;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessage;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessageHandler;
use App\Entity\CustomDomain;
use App\Entity\CustomDomainIntent;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Service\Hosting\CustomDomain\CustomDomainIntentService;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Service\Hosting\Exception\PendingHostingChangeException;
use App\Service\Hosting\HostingChangeService;
use App\Service\Route\PermalinkService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\CustomDomainIntentFactory;
use App\Tests\Factory\HostingChangeFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(HostingChangeService::class)]
#[CoversClass(CustomDomainService::class)]
#[CoversClass(CustomDomainIntentService::class)]
class HostingChangeServiceTest extends KernelTestCase
{
    /** @throws PendingHostingChangeException */
    public function test_succeeds_updates_blog_and_dispatches_event(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);

        $service = $this->getService(HostingChangeService::class);

        $hostingChange = $service->startHostingChange($blog, BlogHostingAt::SELF, toHostingUrl: 'https://example.com');
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());

        $service->process($hostingChange);

        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($blog);
        $this->assertSame(BlogHostingAt::SELF, $blog->getHostingAt());
        $this->assertSame('https://example.com', $blog->getHostingUrl());

        // cache is cleared via the dispatched event, not directly by the service
        $this->getEd()->assertDispatched(BlogHostingChangedEvent::class);
        $this->getEd()->assertDispatched(CacheClearAllEvent::class);
    }

    /** @throws PendingHostingChangeException */
    public function test_failure_rolls_back_and_propagates_the_exception_leaving_change_as_changing(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);

        $failingUrlsService = new class extends UpdateBlogUrlsMessageHandler {
            public function __construct()
            {
            }
            public function __invoke(UpdateBlogUrlsMessage $message): void
            {
                throw new \RuntimeException('boom');
            }
        };

        $service = new HostingChangeService(
            $this->getService(EntityManagerInterface::class),
            $this->getService(MessageBusInterface::class),
            $this->getService(PermalinkService::class),
            $this->getService(CustomDomainService::class),
            $this->getService(CustomDomainIntentService::class),
            $this->getService(EventDispatcherInterface::class),
            $failingUrlsService,
            $this->getService(LoggerInterface::class),
        );

        $hostingChange = $service->startHostingChange($blog, BlogHostingAt::SELF, toHostingUrl: 'https://example.com');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('boom');

        try {
            $service->process($hostingChange);
        } finally {
            // wrapInTransaction() closes the EntityManager on failure, so we can only rely
            // on the in-memory state of the objects we already hold, not re-fetch via the EM
            $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
            $this->assertSame(BlogHostingAt::SUBDOMAIN, $blog->getHostingAt());
        }
    }

    /** @throws PendingHostingChangeException */
    public function test_request_hosting_change_rejects_when_a_change_is_already_pending(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        HostingChangeFactory::createOne(['blog' => $blog, 'status' => HostingChangeStatus::CHANGING]);

        $service = $this->getService(HostingChangeService::class);

        $this->assertTrue($service->hasPendingChange($blog));

        $this->expectException(PendingHostingChangeException::class);
        $service->startHostingChange($blog, BlogHostingAt::SELF, toHostingUrl: 'https://example.com');
    }

    /** @throws PendingHostingChangeException */
    public function test_request_hosting_change_allowed_when_previous_change_resolved(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        HostingChangeFactory::createOne(['blog' => $blog, 'status' => HostingChangeStatus::SUCCESS]);

        $service = $this->getService(HostingChangeService::class);

        $this->assertFalse($service->hasPendingChange($blog));

        $hostingChange = $service->startHostingChange($blog, BlogHostingAt::SELF, toHostingUrl: 'https://example.com');
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
    }

    /** @throws PendingHostingChangeException */
    public function test_creates_custom_domain_when_change_completes_to_domain(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $intent = CustomDomainIntentFactory::createFor($blog, 'mysite.com', [
            'tls_provider' => CustomDomainTlsProvider::AUTO,
            'certificate' => 'intent-cert-pem',
            'private_key_encrypted' => 'intent-key-encrypted',
            'valid_from' => new \DateTimeImmutable('-1 day'),
            'valid_to' => new \DateTimeImmutable('+89 days'),
        ]);
        $intentId = $intent->getId();

        $service = $this->getService(HostingChangeService::class);

        $hostingChange = $service->startHostingChange($blog, BlogHostingAt::DOMAIN, toDomain: 'mysite.com');
        $service->process($hostingChange);

        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($blog);
        $this->assertSame(BlogHostingAt::DOMAIN, $blog->getHostingAt());
        $this->assertNotNull($blog->getCustomDomain());
        $this->assertSame('mysite.com', $blog->getCustomDomain()->getDomain());
        $this->assertSame(CustomDomainTlsProvider::AUTO, $blog->getCustomDomain()->getTlsProvider());
        $this->assertSame('intent-cert-pem', $blog->getCustomDomain()->getCertificate());

        $this->assertNull($this->getEm()->getRepository(CustomDomainIntent::class)->find($intentId));
    }

    /** @throws PendingHostingChangeException */
    public function test_deletes_custom_domain_when_moving_away_from_domain(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::DOMAIN, 'subdomain' => 'hcs-away-from-domain']);
        $customDomain = CustomDomainFactory::createActiveFor($blog, 'active.com');
        $customDomainId = $customDomain->getId();

        $service = $this->getService(HostingChangeService::class);

        $hostingChange = $service->startHostingChange($blog, BlogHostingAt::SUBDOMAIN, toSubdomain: 'hcs-away-from-domain');
        $service->process($hostingChange);

        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($blog);
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $blog->getHostingAt());
        $this->assertNull($blog->getCustomDomain());

        $this->assertNull($this->getEm()->getRepository(CustomDomain::class)->find($customDomainId));
    }
}
