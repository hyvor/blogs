<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'blogs:fix-meta-type',
    description: 'Backfills the #type discriminator on the blogs.meta JSON column for older records',
)]
class FixBlogMetaTypeCommand extends Command
{
    private const META_TYPE = 'blogs_meta';

    public function __construct(
        private Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write any changes, only report what would change');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool)$input->getOption('dry-run');

        $rows = $this->connection->fetchAllAssociative('SELECT id, meta FROM blogs');

        $updated = 0;
        foreach ($rows as $row) {
            /** @var int $blogId */
            $blogId = $row['id'];
            /** @var string|null $rawMeta */
            $rawMeta = $row['meta'];
            $meta = $rawMeta !== null ? json_decode($rawMeta, true) : null;

            if (!is_array($meta)) {
                $meta = [];
            }

            if (array_key_exists('#type', $meta)) {
                continue;
            }

            $meta['#type'] = self::META_TYPE;
            $updated++;

            if ($dryRun) {
                $io->writeln(sprintf('Blog #%d: %s', $blogId, json_encode($meta) ?: ''));
                continue;
            }

            $this->connection->executeStatement(
                'UPDATE blogs SET meta = :meta::jsonb WHERE id = :id',
                [
                    'meta' => json_encode($meta),
                    'id' => $blogId,
                ]
            );
        }

        $io->success(sprintf(
            '%s %d of %d blog(s).',
            $dryRun ? 'Would update' : 'Updated',
            $updated,
            count($rows)
        ));

        return Command::SUCCESS;
    }
}
