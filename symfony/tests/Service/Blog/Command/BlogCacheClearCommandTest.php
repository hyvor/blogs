<?php

namespace App\Tests\Service\Blog\Command;

use App\Service\Blog\Command\BlogCacheClearCommand;
use App\Service\Cache\Event\CacheClearAllEvent;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(BlogCacheClearCommand::class)]
class BlogCacheClearCommandTest extends KernelTestCase
{

    public function test_fails_without_subdomain_or_id(): void
    {
        $commandTester = $this->getCommandTester('app:blog:clear-cache');
        $commandTester->execute([]);

        $this->assertStringContainsString(
            'You must provide either a subdomain or an id',
            $commandTester->getDisplay(),
        );
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->getEd()->assertNotDispatched(CacheClearAllEvent::class);
    }

    public function test_fails_when_blog_not_found_by_subdomain(): void
    {
        $commandTester = $this->getCommandTester('app:blog:clear-cache');
        $commandTester->execute(['subdomain' => 'does-not-exist']);

        $this->assertStringContainsString('Blog not found', $commandTester->getDisplay());
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->getEd()->assertNotDispatched(CacheClearAllEvent::class);
    }

    public function test_fails_when_blog_not_found_by_id(): void
    {
        $commandTester = $this->getCommandTester('app:blog:clear-cache');
        $commandTester->execute(['id' => 999999]);

        $this->assertStringContainsString('Blog not found', $commandTester->getDisplay());
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->getEd()->assertNotDispatched(CacheClearAllEvent::class);
    }

    public function test_clears_cache_by_subdomain(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'cache-clear-subdomain']);

        $commandTester = $this->getCommandTester('app:blog:clear-cache');
        $commandTester->execute(['subdomain' => $blog->getSubdomain()]);

        $this->assertStringContainsString(
            "Cleared cache for blog. Subdomain: {$blog->getSubdomain()}, ID: {$blog->getId()}",
            $commandTester->getDisplay(),
        );

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $event = $this->getEd()->getFirstEvent(CacheClearAllEvent::class);
        $this->assertSame($blog->getId(), $event->blog->getId());
    }

    public function test_clears_cache_by_id(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'cache-clear-id']);

        $commandTester = $this->getCommandTester('app:blog:clear-cache');
        $commandTester->execute(['id' => $blog->getId()]);

        $this->assertStringContainsString(
            "Cleared cache for blog. Subdomain: {$blog->getSubdomain()}, ID: {$blog->getId()}",
            $commandTester->getDisplay(),
        );

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $event = $this->getEd()->getFirstEvent(CacheClearAllEvent::class);
        $this->assertSame($blog->getId(), $event->blog->getId());
    }

    public function test_clears_cache_for_all_blogs(): void
    {
        $blog1 = BlogFactory::createOne(['subdomain' => 'cache-clear-all-1']);
        $blog2 = BlogFactory::createOne(['subdomain' => 'cache-clear-all-2']);

        $commandTester = $this->getCommandTester('app:blog:clear-cache');
        $commandTester->execute(['--all' => true]);

        $this->assertStringContainsString('Clearing cache for all blogs', $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->getEd()->assertDispatchedCount(CacheClearAllEvent::class, 2);

        $blogIds = array_map(
            fn(CacheClearAllEvent $event) => $event->blog->getId(),
            array_filter(
                $this->getEd()->getDispatchedEvents(),
                fn($event) => $event instanceof CacheClearAllEvent,
            ),
        );
        $this->assertContains($blog1->getId(), $blogIds);
        $this->assertContains($blog2->getId(), $blogIds);
    }

}
