<?php

namespace App\Tests\Service\Hosting;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\HostingChange;
use App\Service\Hosting\HostingChangeService;
use App\Service\Hosting\Message\HostingChangeMessage;
use App\Service\Hosting\MessageHandler\HostingChangeMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HostingChangeFactory;
use Doctrine\Persistence\ManagerRegistry;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[CoversClass(HostingChangeMessageHandler::class)]
#[CoversClass(HostingChangeMessage::class)]
#[CoversClass(HostingChangeService::class)]
class HostingChangeMessageHandlerTest extends KernelTestCase
{
    public function test_throws_unrecoverable_when_hosting_change_not_found(): void
    {
        $handler = $this->getService(HostingChangeMessageHandler::class);
        $this->expectException(UnrecoverableMessageHandlingException::class);
        $handler(new HostingChangeMessage(999999999));
    }

    public function test_subdomain_to_self(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN, 'subdomain' => 'from-subdomain']);
        $hostingChange = HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SUBDOMAIN,
            'from_subdomain' => 'from-subdomain',
            'to_at' => BlogHostingAt::SELF,
            'to_hosting_url' => 'https://example.com',
        ]);

        $t = $this->transport('async')->throwExceptions();
        $t->send(new HostingChangeMessage($hostingChange->getId()));
        $t->processOrFail();

        $hostingChange = $this->getEm()->find(HostingChange::class, $hostingChange->getId());
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($blog);
        $this->assertSame(BlogHostingAt::SELF, $blog->getHostingAt());
        $this->assertSame('https://example.com', $blog->getHostingUrl());
    }

    public function test_self_to_subdomain(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SELF, 'hosting_url' => 'https://from-hosting.com']);
        $hostingChange = HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SELF,
            'from_hosting_url' => 'https://from-hosting.com',
            'to_at' => BlogHostingAt::SUBDOMAIN,
            'to_subdomain' => 'to-subdomain',
        ]);

        $t = $this->transport('async')->throwExceptions();
        $t->send(new HostingChangeMessage($hostingChange->getId()));
        $t->processOrFail();

        $hostingChange = $this->getEm()->find(HostingChange::class, $hostingChange->getId());
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($blog);
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $blog->getHostingAt());
        $this->assertSame('to-subdomain', $blog->getSubdomain());
    }

    public function test_change_subdomain(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN, 'subdomain' => 'from-subdomain']);
        $hostingChange = HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SUBDOMAIN,
            'from_subdomain' => 'from-subdomain',
            'to_at' => BlogHostingAt::SUBDOMAIN,
            'to_subdomain' => 'to-subdomain',
        ]);

        $t = $this->transport('async')->throwExceptions();
        $t->send(new HostingChangeMessage($hostingChange->getId()));
        $t->processOrFail();

        $hostingChange = $this->getEm()->find(HostingChange::class, $hostingChange->getId());
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($blog);
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $blog->getHostingAt());
        $this->assertSame('to-subdomain', $blog->getSubdomain());
    }

    public function test_change_self(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SELF, 'hosting_url' => 'https://from-hosting.com']);
        $hostingChange = HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SELF,
            'from_hosting_url' => 'https://from-hosting.com',
            'to_at' => BlogHostingAt::SELF,
            'to_hosting_url' => 'https://to-hosting.com',
        ]);

        $t = $this->transport('async')->throwExceptions();
        $t->send(new HostingChangeMessage($hostingChange->getId()));
        $t->processOrFail();

        $hostingChange = $this->getEm()->find(HostingChange::class, $hostingChange->getId());
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::SUCCESS, $hostingChange->getStatus());

        $blog = $this->getEm()->getRepository(Blog::class)->find($blog->getId());
        $this->assertNotNull($blog);
        $this->assertSame(BlogHostingAt::SELF, $blog->getHostingAt());
        $this->assertSame('https://to-hosting.com', $blog->getHostingUrl());
    }

    public function test_redispatches_with_increasing_delay_then_marks_failed(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $hostingChange = HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SUBDOMAIN,
            'to_at' => BlogHostingAt::SELF,
            'to_hosting_url' => 'https://example.com',
        ]);
        $hostingChangeId = $hostingChange->getId();

        $failingService = new class extends HostingChangeService {
            public function __construct()
            {
            }

            public function process(HostingChange $hostingChange): void
            {
                throw new \RuntimeException('boom');
            }
        };

        $handler = new HostingChangeMessageHandler(
            $this->getService(ManagerRegistry::class),
            $failingService,
            $this->getService(MessageBusInterface::class),
            $this->getService(LoggerInterface::class),
        );

        // attempt 1: fails, retry_count = 1, redispatched with a delay
        $handler(new HostingChangeMessage($hostingChangeId));

        $hostingChange = $this->getEm()->find(HostingChange::class, $hostingChangeId);
        $this->assertNotNull($hostingChange);
        $this->assertSame(1, $hostingChange->getRetryCount());
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());

        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(HostingChangeMessage::class, 1);
        $firstDelay = $dispatched->first(HostingChangeMessage::class)->last(DelayStamp::class);
        $this->assertNotNull($firstDelay);
        $this->assertSame(60000, $firstDelay->getDelay());

        // attempt 2: fails, retry_count = 2, redispatched with a bigger delay
        $handler(new HostingChangeMessage($hostingChangeId));

        $hostingChange = $this->getEm()->find(HostingChange::class, $hostingChangeId);
        $this->assertNotNull($hostingChange);
        $this->assertSame(2, $hostingChange->getRetryCount());
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());

        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(HostingChangeMessage::class, 2);
        $envelopes = array_values(array_filter(
            $dispatched->all(),
            fn($e) => $e->getMessage() instanceof HostingChangeMessage
        ));
        $secondDelay = $envelopes[1]->last(DelayStamp::class);
        $this->assertNotNull($secondDelay);
        $this->assertSame(300000, $secondDelay->getDelay());

        // attempt 3: fails, exhausted -> marked as failed, no further redispatch
        $handler(new HostingChangeMessage($hostingChangeId));

        $hostingChange = $this->getEm()->find(HostingChange::class, $hostingChangeId);
        $this->assertNotNull($hostingChange);
        $this->assertSame(3, $hostingChange->getRetryCount());
        $this->assertSame(HostingChangeStatus::FAILED, $hostingChange->getStatus());
        $this->assertSame('boom', $hostingChange->getErrorMessage());

        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(HostingChangeMessage::class, 2);
    }
}
