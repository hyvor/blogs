<?php

namespace App\Service\Hosting\CustomDomain\Command;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'hosting:migrate-caddy-tls-certificates',
    description: 'Migrates TLS certificates previously managed by Caddy into the custom_domains table',
)]
class MigrateCaddyTlsCertificatesCommand
{
    use ClockAwareTrait;

    public function __construct(
        private Connection $connection,
        private EntityManagerInterface $em,
        private CustomDomainService $customDomainService,
        private Encryption $encryption,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option('root directory containing one subfolder per domain, each with <domain>.crt/.key')] string $dir = '/app/backend/var/caddy-certs',
        #[Option('only validate the certificate files, do not create any records')] bool $dryRun = false,
    ): int {
        $io = new SymfonyStyle($input, $output);

        if (!is_dir($dir)) {
            $io->error("Directory not found: $dir");
            return Command::FAILURE;
        }

        $entries = $this->readEntries($dir, $io);

        if ($entries === []) {
            $io->warning("No domain folders found in $dir");
            return Command::SUCCESS;
        }

        $io->info(sprintf(
            'Found %d domain folder(s) in %s',
            count($entries),
            $dir,
        ));

        if (!$dryRun) {
            $confirmed = $io->confirm(
                'Are you sure you want to migrate these TLS certificates into the custom_domains table?. Make sure to run with --dry-run before', false);
            if (!$confirmed) {
                $io->warning('Migration aborted by user.');
                return Command::SUCCESS;
            }
        }

        $migrated = 0;
        $skipped = 0;
        $invalid = 0;

        foreach ($entries as $entry) {
            $domain = $entry['domain'];

            $io->section("Processing domain: $domain");

            $validated = $this->validate($entry, $io);
            if ($validated === null) {
                $invalid++;
                continue;
            }
            [$validFrom, $validTo] = $validated;

            $io->info(sprintf(
                '[%s] certificate valid from %s to %s',
                $domain,
                $validFrom->format(DATE_ATOM),
                $validTo->format(DATE_ATOM),
            ));

            if ($dryRun) {
                continue;
            }

            if ($this->migrateOne($entry, $validFrom, $validTo, $io)) {
                $migrated++;
            } else {
                $skipped++;
            }
        }

        $io->success(sprintf(
            '%s%d migrated, %d skipped, %d invalid (out of %d domain folder(s))',
            $dryRun ? '[dry-run] ' : '',
            $migrated,
            $skipped,
            $invalid,
            count($entries),
        ));

        return Command::SUCCESS;
    }

    /**
     * @return list<array{domain: string, crt: string, key: string}>
     */
    private function readEntries(string $dir, SymfonyStyle $io): array
    {
        $domains = array_values(array_filter(
            scandir($dir) ?: [],
            fn(string $entry) => $entry !== '.' && $entry !== '..' && is_dir("$dir/$entry"),
        ));
        sort($domains);

        $entries = [];

        foreach ($domains as $domain) {
            $domainDir = "$dir/$domain";
            $crtPath = "$domainDir/$domain.crt";
            $keyPath = "$domainDir/$domain.key";

            if (!is_file($crtPath) || !is_file($keyPath)) {
                $io->warning("[$domain] missing .crt/.key file(s), skipping folder");
                continue;
            }

            $entries[] = [
                'domain' => $domain,
                'crt' => (string) file_get_contents($crtPath),
                'key' => (string) file_get_contents($keyPath),
            ];
        }

        return $entries;
    }

    /**
     * Validates that the crt/key for a domain are well-formed and consistent, and derives the
     * certificate's validity period.
     *
     * @param array{domain: string, crt: string, key: string} $entry
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable}|null
     */
    private function validate(array $entry, SymfonyStyle $io): ?array
    {
        $domain = $entry['domain'];

        $privateKey = openssl_pkey_get_private($entry['key']);
        if ($privateKey === false) {
            $io->error("[$domain] Invalid private key (.key file is not a valid PEM private key)");
            return null;
        }

        $cert = openssl_x509_read($entry['crt']);
        if ($cert === false) {
            $io->error("[$domain] Invalid certificate (.crt file is not a valid PEM certificate)");
            return null;
        }

        if (!openssl_x509_check_private_key($cert, $privateKey)) {
            $io->error("[$domain] Private key does not match the certificate");
            return null;
        }

        $parsed = openssl_x509_parse($cert);
        $validFromTimestamp = $parsed !== false ? ($parsed['validFrom_time_t'] ?? null) : null;
        $validToTimestamp = $parsed !== false ? ($parsed['validTo_time_t'] ?? null) : null;

        if (!is_int($validFromTimestamp) || !is_int($validToTimestamp)) {
            $io->error("[$domain] Unable to determine the certificate's validity period");
            return null;
        }

        return [
            (new \DateTimeImmutable())->setTimestamp($validFromTimestamp),
            (new \DateTimeImmutable())->setTimestamp($validToTimestamp),
        ];
    }

    /**
     * @param array{domain: string, crt: string, key: string} $entry
     */
    private function migrateOne(
        array $entry,
        \DateTimeImmutable $validFrom,
        \DateTimeImmutable $validTo,
        SymfonyStyle $io
    ): bool {
        $domain = $entry['domain'];

        if ($this->customDomainService->getCustomDomain($domain) !== null) {
            $io->info("[$domain] custom_domains record already exists, skipping");
            return false;
        }

        $blogId = $this->connection->fetchOne(
            'SELECT id FROM blogs WHERE hosting_domain = :domain AND deleted_at IS NULL',
            ['domain' => $domain],
        );

        if (!is_int($blogId) && !is_string($blogId)) {
            $io->warning("[$domain] No blog found for this domain, skipping");
            return false;
        }

        $blog = $this->em->find(Blog::class, (int) $blogId);
        if ($blog === null) {
            $io->warning("[$domain] Blog #$blogId not found, skipping");
            return false;
        }

        if ($blog->getHostingAt() !== BlogHostingAt::DOMAIN) {
            $io->warning("[$domain] Blog #{$blog->getId()} hosting_at is '{$blog->getHostingAt()->value}', not 'domain', skipping");
            return false;
        }

        $customDomain = new CustomDomain();
        $customDomain->setBlog($blog);
        $customDomain->setDomain($domain);
        $customDomain->setTlsProvider(CustomDomainTlsProvider::AUTO);
        $customDomain->setPrivateKeyEncrypted($this->encryption->encryptString($entry['key']));
        $customDomain->setCertificate($entry['crt']);
        $customDomain->setValidFrom($validFrom);
        $customDomain->setValidTo($validTo);
        $customDomain->setCreatedAt($this->now());
        $customDomain->setUpdatedAt($this->now());

        $this->em->persist($customDomain);
        $blog->setCustomDomain($customDomain);
        $this->em->persist($blog);
        $this->em->flush();

        $io->info("[$domain] Migrated TLS certificate for blog #{$blog->getId()}");

        return true;
    }
}
