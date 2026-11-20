<?php

namespace App\Command\Migration;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The "self" hosting option (hosting_at = self) was removed.
 * Instead, any blog can set a hosting URL (blogs.hosting_url) where the blog is finally served
 * (ex: a reverse proxy or a headless frontend), which is then used for URL generation.
 *
 * This command:
 * - clears stale hosting_url of non-self blogs (it was only meaningful for self hosting before)
 * - migrates self-hosted blogs to subdomain hosting, keeping their hosting_url (so their URLs do not change)
 * - fails pending hosting changes from/to self, and rewrites self in the hosting history to subdomain
 *
 * Plain SQL is used since BlogHostingAt::SELF no longer exists (Doctrine cannot hydrate those rows).
 */
#[AsCommand(
    name: 'blogs:migrate-self-hosting',
    description: 'Migrates self-hosted blogs (hosting_at = self) to subdomain hosting with a hosting URL',
)]
class MigrateSelfHostingCommand extends Command
{

    public function __construct(
        private Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not save any changes. Only reports what would be changed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $this->connection->beginTransaction();

        try {
            $staleHostingUrls = $this->connection->executeStatement(
                "UPDATE blogs SET hosting_url = NULL WHERE hosting_at <> 'self' AND hosting_url IS NOT NULL"
            );

            $selfBlogs = $this->connection->executeStatement(
                "UPDATE blogs SET hosting_at = 'subdomain', hosting_url = NULLIF(RTRIM(TRIM(hosting_url), '/'), '') WHERE hosting_at = 'self'"
            );

            $pendingChanges = $this->connection->executeStatement(
                "UPDATE hosting_changes SET status = 'failed', error_message = 'Self hosting was removed. Set a hosting URL instead.', updated_at = NOW()
                WHERE status = 'changing' AND (from_at = 'self' OR to_at = 'self')"
            );

            $historyFrom = $this->connection->executeStatement(
                "UPDATE hosting_changes SET from_at = 'subdomain' WHERE from_at = 'self'"
            );
            $historyTo = $this->connection->executeStatement(
                "UPDATE hosting_changes SET to_at = 'subdomain' WHERE to_at = 'self'"
            );

            $dryRun ? $this->connection->rollBack() : $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $io->definitionList(
            ['Self-hosted blogs migrated to subdomain' => $selfBlogs],
            ['Stale hosting URLs cleared (non-self blogs)' => $staleHostingUrls],
            ['Pending self hosting changes failed' => $pendingChanges],
            ['Hosting history rows updated' => $historyFrom + $historyTo],
        );

        if ($dryRun) {
            $io->note('Dry run: no changes were saved');
        }

        $io->success('Done');
        return Command::SUCCESS;
    }

}
