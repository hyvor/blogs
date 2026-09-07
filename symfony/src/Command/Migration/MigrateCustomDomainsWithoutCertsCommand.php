<?php

namespace App\Command\Migration;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// certs that were not migrated via MigrateCaddyTlsCertificatesCommand
#[AsCommand(
    name: 'hosting:migrate-custom-domains-without-certs',
    description: 'Migrates custom domains without TLS certificates into the custom_domains table',
)]
class MigrateCustomDomainsWithoutCertsCommand
{
    use ClockAwareTrait;

    public function __construct(
        private Connection $connection,
        private EntityManagerInterface $em,
        private CustomDomainService $customDomainService,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option('only report what would be migrated, do not create any records')] bool $dryRun = false,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, hosting_domain FROM blogs WHERE hosting_at = :hostingAt AND hosting_domain IS NOT NULL AND deleted_at IS NULL',
            ['hostingAt' => BlogHostingAt::DOMAIN->value],
        );

        if ($rows === []) {
            $io->warning('No blogs found with hosting_at = domain and a hosting_domain set.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d blog(s) to process', count($rows)));

        $migrated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $blogId = (int) $row['id'];
            $domain = (string) $row['hosting_domain'];

            $io->section("Processing blog #$blogId ($domain)");

            if ($this->customDomainService->getCustomDomain($domain) !== null) {
                $io->info("[$domain] custom_domains record already exists, skipping");
                $skipped++;
                continue;
            }

            $blog = $this->em->find(Blog::class, $blogId);
            if ($blog === null) {
                $io->warning("[$domain] Blog #$blogId not found, skipping");
                $skipped++;
                continue;
            }

            if ($blog->getCustomDomain() !== null) {
                $io->info("[$domain] Blog #$blogId already has a custom domain, skipping");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $io->info("[$domain] would create custom_domains record for blog #$blogId");
                $migrated++;
                continue;
            }

            $customDomain = new CustomDomain();
            $customDomain->setBlog($blog);
            $customDomain->setDomain($domain);
            $customDomain->setTlsProvider(CustomDomainTlsProvider::CUSTOM);
            $customDomain->setPrivateKeyEncrypted(null);
            $customDomain->setCertificate(null);
            $customDomain->setValidFrom(null);
            $customDomain->setValidTo(null);
            $customDomain->setCreatedAt($this->now());
            $customDomain->setUpdatedAt($this->now());

            $this->em->persist($customDomain);
            $blog->setCustomDomain($customDomain);
            $this->em->persist($blog);
            $this->em->flush();

            $io->info("[$domain] Created custom domain for blog #$blogId");

            $migrated++;
        }

        $io->success(sprintf(
            '%s%d migrated, %d skipped (out of %d blog(s))',
            $dryRun ? '[dry-run] ' : '',
            $migrated,
            $skipped,
            count($rows),
        ));

        return Command::SUCCESS;
    }
}
