<?php

namespace App\Tests\Service\Hosting\CustomDomain\Command;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\BlogHostingAt;
use App\Service\Hosting\CustomDomain\Command\MigrateCaddyTlsCertificatesCommand;
use App\Tests\Factory\BlogFactory;
use App\Tests\Helper\SelfSignedCertificate;
use Doctrine\DBAL\Connection;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(MigrateCaddyTlsCertificatesCommand::class)]
class MigrateCaddyTlsCertificatesCommandTest extends KernelTestCase
{
    private const string COMMAND = 'hosting:migrate-caddy-tls-certificates';

    private string $dir;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem();
        $this->dir = sys_get_temp_dir() . '/caddy-certs-test-' . bin2hex(random_bytes(8));
        $this->filesystem->mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->dir);
        parent::tearDown();
    }

    private function writeDomainFolder(
        string $domain,
        ?string $keyPemOverride = null,
        ?string $crtPemOverride = null,
    ): void {
        $domainDir = "$this->dir/$domain";
        $this->filesystem->mkdir($domainDir);

        $cert = SelfSignedCertificate::generate($domain);

        $this->filesystem->dumpFile("$domainDir/$domain.key", $keyPemOverride ?? $cert['privateKeyPem']);
        $this->filesystem->dumpFile("$domainDir/$domain.crt", $crtPemOverride ?? $cert['certificatePem']);
    }

    private function setBlogHostingDomain(Blog $blog, string $domain): void
    {
        $this->getService(Connection::class)->executeStatement(
            'UPDATE blogs SET hosting_domain = :domain WHERE id = :id',
            ['domain' => $domain, 'id' => $blog->getId()],
        );
    }

    public function test_fails_when_directory_not_found(): void
    {
        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->execute(['--dir' => "$this->dir/does-not-exist"]);

        $this->assertStringContainsString('Directory not found', $commandTester->getDisplay());
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function test_migrates_valid_certificate_for_matching_blog(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::DOMAIN]);
        $this->setBlogHostingDomain($blog, 'migrate-example.com');
        $this->writeDomainFolder('migrate-example.com');

        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['--dir' => $this->dir]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Migrated TLS certificate for blog', $commandTester->getDisplay());
        $this->assertStringContainsString('1 migrated, 0 skipped, 0 invalid', $commandTester->getDisplay());

        $this->getEm()->clear();
        $customDomain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'migrate-example.com']);
        $this->assertNotNull($customDomain);
        $this->assertSame($blog->getId(), $customDomain->getBlog()->getId());
        $this->assertNotNull($customDomain->getPrivateKeyEncrypted());
        $this->assertNotNull($customDomain->getCertificate());
        $this->assertNotNull($customDomain->getValidFrom());
        $this->assertNotNull($customDomain->getValidTo());

        $refreshedBlog = $this->getEm()->find(Blog::class, $blog->getId());
        $this->assertInstanceOf(Blog::class, $refreshedBlog);
        $this->assertNotNull($refreshedBlog->getCustomDomain());
        $this->assertSame($customDomain->getId(), $refreshedBlog->getCustomDomain()->getId());
    }

    public function test_dry_run_validates_without_creating_records(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::DOMAIN]);
        $this->setBlogHostingDomain($blog, 'dry-run-example.com');
        $this->writeDomainFolder('dry-run-example.com');

        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->execute(['--dir' => $this->dir, '--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('certificate valid from', $commandTester->getDisplay());
        $this->assertStringContainsString('[dry-run] 0 migrated', $commandTester->getDisplay());

        $customDomain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'dry-run-example.com']);
        $this->assertNull($customDomain);
    }

    public function test_skips_when_no_matching_blog(): void
    {
        $this->writeDomainFolder('orphan-example.com');

        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['--dir' => $this->dir]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('No blog found for this domain', $commandTester->getDisplay());
        $this->assertStringContainsString('0 migrated, 1 skipped, 0 invalid', $commandTester->getDisplay());
    }

    public function test_skips_when_blog_hosting_at_is_not_domain(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $this->setBlogHostingDomain($blog, 'not-domain-hosted.com');
        $this->writeDomainFolder('not-domain-hosted.com');

        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['--dir' => $this->dir]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString("hosting_at is 'subdomain'", $commandTester->getDisplay());
        $this->assertStringContainsString('0 migrated, 1 skipped, 0 invalid', $commandTester->getDisplay());
    }

    public function test_is_idempotent_when_run_twice(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::DOMAIN]);
        $this->setBlogHostingDomain($blog, 'idempotent-example.com');
        $this->writeDomainFolder('idempotent-example.com');

        $firstRun = $this->getCommandTester(self::COMMAND);
        $firstRun->setInputs(['yes']);
        $firstRun->execute(['--dir' => $this->dir]);

        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['--dir' => $this->dir]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('custom_domains record already exists', $commandTester->getDisplay());
        $this->assertStringContainsString('0 migrated, 1 skipped, 0 invalid', $commandTester->getDisplay());

        $count = $this->getEm()->getRepository(CustomDomain::class)->count(['domain' => 'idempotent-example.com']);
        $this->assertSame(1, $count);
    }

    public function test_reports_invalid_when_key_does_not_match_certificate(): void
    {
        $otherCert = SelfSignedCertificate::generate('mismatched.com');
        $this->writeDomainFolder('mismatched.com', keyPemOverride: $otherCert['privateKeyPem']);
        // overwrite the .crt with an unrelated certificate so it doesn't match the .key above
        $unrelated = SelfSignedCertificate::generate('mismatched.com');
        $this->filesystem->dumpFile("$this->dir/mismatched.com/mismatched.com.crt", $unrelated['certificatePem']);

        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['--dir' => $this->dir]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('does not match', $commandTester->getDisplay());
        $this->assertStringContainsString('0 migrated, 0 skipped, 1 invalid', $commandTester->getDisplay());
    }

    public function test_aborts_when_confirmation_declined(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::DOMAIN]);
        $this->setBlogHostingDomain($blog, 'declined-example.com');
        $this->writeDomainFolder('declined-example.com');

        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->setInputs(['no']);
        $commandTester->execute(['--dir' => $this->dir]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Migration aborted by user', $commandTester->getDisplay());

        $customDomain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'declined-example.com']);
        $this->assertNull($customDomain);
    }

    public function test_does_not_prompt_for_confirmation_on_dry_run(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::DOMAIN]);
        $this->setBlogHostingDomain($blog, 'dry-run-no-prompt.com');
        $this->writeDomainFolder('dry-run-no-prompt.com');

        $commandTester = $this->getCommandTester(self::COMMAND);
        // no setInputs(): would throw if the command tried to read an answer
        $commandTester->execute(['--dir' => $this->dir, '--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringNotContainsString('Are you sure', $commandTester->getDisplay());
    }

    public function test_skips_folder_missing_required_files(): void
    {
        $this->filesystem->mkdir("$this->dir/incomplete.com");
        $this->filesystem->dumpFile("$this->dir/incomplete.com/incomplete.com.crt", 'not-a-real-cert');

        $commandTester = $this->getCommandTester(self::COMMAND);
        $commandTester->execute(['--dir' => $this->dir]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('missing .crt/.key file(s)', $commandTester->getDisplay());
        $this->assertStringContainsString('No domain folders found', $commandTester->getDisplay());
    }
}
