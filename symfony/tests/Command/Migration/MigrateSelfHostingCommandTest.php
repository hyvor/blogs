<?php

namespace App\Tests\Command\Migration;

use App\Command\Migration\MigrateSelfHostingCommand;
use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\HostingChange;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\HostingChangeFactory;
use Doctrine\DBAL\Connection;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(MigrateSelfHostingCommand::class)]
class MigrateSelfHostingCommandTest extends KernelTestCase
{

    private function connection(): Connection
    {
        return $this->getService(Connection::class);
    }

    /**
     * BlogHostingAt::SELF no longer exists, so legacy rows are created with SQL
     */
    private function createSelfHostedBlog(string $hostingUrl): Blog
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $this->connection()->executeStatement(
            "UPDATE blogs SET hosting_at = 'self', hosting_url = ? WHERE id = ?",
            [$hostingUrl, $blog->getId()]
        );
        return $blog;
    }

    private function hostingAtOf(Blog $blog): string
    {
        $hostingAt = $this->connection()->fetchOne('SELECT hosting_at FROM blogs WHERE id = ?', [$blog->getId()]);
        $this->assertIsString($hostingAt);
        return $hostingAt;
    }

    public function test_migrates_self_hosted_blogs_to_subdomain_keeping_hosting_url(): void
    {
        $selfBlog = $this->createSelfHostedBlog('https://example.com/blog/');
        $subdomainBlog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);

        $commandTester = $this->getCommandTester('blogs:migrate-self-hosting');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Self-hosted blogs migrated to subdomain', $commandTester->getDisplay());

        $this->getEm()->clear();

        $migrated = $this->getEm()->find(Blog::class, $selfBlog->getId());
        $this->assertNotNull($migrated);
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $migrated->getHostingAt());
        $this->assertSame('https://example.com/blog', $migrated->getHostingUrl());

        $untouched = $this->getEm()->find(Blog::class, $subdomainBlog->getId());
        $this->assertNotNull($untouched);
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $untouched->getHostingAt());
        $this->assertNull($untouched->getHostingUrl());
    }

    public function test_clears_stale_hosting_url_of_non_self_blogs(): void
    {
        $subdomainBlog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN, 'hosting_url' => 'https://stale.com']);
        $domainBlog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::DOMAIN, 'hosting_url' => 'https://stale2.com']);
        $domainBlog->setCustomDomain(CustomDomainFactory::createActiveFor($domainBlog, 'blog.example.com'));
        $this->getEm()->flush();

        $this->getCommandTester('blogs:migrate-self-hosting')->execute([]);
        $this->getEm()->clear();

        $subdomainBlog = $this->getEm()->find(Blog::class, $subdomainBlog->getId());
        $this->assertNotNull($subdomainBlog);
        $this->assertNull($subdomainBlog->getHostingUrl());

        $domainBlog = $this->getEm()->find(Blog::class, $domainBlog->getId());
        $this->assertNotNull($domainBlog);
        $this->assertSame(BlogHostingAt::DOMAIN, $domainBlog->getHostingAt());
        $this->assertNull($domainBlog->getHostingUrl());
    }

    public function test_fails_pending_self_changes_and_rewrites_history(): void
    {
        $blog = $this->createSelfHostedBlog('https://example.com');

        $pending = HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SUBDOMAIN,
            'to_at' => BlogHostingAt::SUBDOMAIN,
            'status' => HostingChangeStatus::CHANGING,
        ]);
        $done = HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SUBDOMAIN,
            'to_at' => BlogHostingAt::SUBDOMAIN,
            'status' => HostingChangeStatus::SUCCESS,
        ]);
        $this->connection()->executeStatement("UPDATE hosting_changes SET to_at = 'self' WHERE id = ?", [$pending->getId()]);
        $this->connection()->executeStatement("UPDATE hosting_changes SET from_at = 'self' WHERE id = ?", [$done->getId()]);

        $this->getCommandTester('blogs:migrate-self-hosting')->execute([]);
        $this->getEm()->clear();

        $pending = $this->getEm()->find(HostingChange::class, $pending->getId());
        $this->assertNotNull($pending);
        $this->assertSame(HostingChangeStatus::FAILED, $pending->getStatus());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $pending->getToAt());
        $this->assertNotNull($pending->getErrorMessage());

        $done = $this->getEm()->find(HostingChange::class, $done->getId());
        $this->assertNotNull($done);
        $this->assertSame(HostingChangeStatus::SUCCESS, $done->getStatus());
        $this->assertSame(BlogHostingAt::SUBDOMAIN, $done->getFromAt());
    }

    public function test_dry_run_changes_nothing(): void
    {
        $blog = $this->createSelfHostedBlog('https://example.com');

        $commandTester = $this->getCommandTester('blogs:migrate-self-hosting');
        $commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Dry run', $commandTester->getDisplay());
        $this->assertSame('self', $this->hostingAtOf($blog));
    }

}
