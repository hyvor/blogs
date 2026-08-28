<?php

namespace App\Command\Migration;

use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Version 2.0.0 (2026-09-01) introduced a new cursor_color field for users.
 * Old instances should run this command to backfill the cursor_color.
 */
#[AsCommand(
    name: 'users:backfill-cursor-colors',
    description: 'Backfills users.cursor_color for users that don\'t have one yet',
)]
class BackfillCursorColorsCommand extends Command
{
    private const int BATCH_SIZE = 500;

    public function __construct(
        private EntityManagerInterface $em,
        private UserService $userService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write any changes, only report how many users would be updated');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $lastId = 0;
        $updated = 0;

        while (true) {
            /** @var User[] $users */
            $users = $this->em->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
                ->where('u.cursor_color IS NULL')
                ->andWhere('u.id > :lastId')
                ->orderBy('u.id', 'ASC')
                ->setMaxResults(self::BATCH_SIZE)
                ->setParameter('lastId', $lastId)
                ->getQuery()
                ->getResult();

            if ($users === []) {
                break;
            }

            foreach ($users as $user) {
                if (!$dryRun) {
                    $user->setCursorColor($this->userService->generateCursorColor());
                }
                $updated++;
            }

            $lastId = end($users)->getId();

            if (!$dryRun) {
                $this->em->flush();
            }
            $this->em->clear();

            $io->writeln(sprintf('Processed up to user #%d (%d updated so far)', $lastId, $updated));
        }

        $io->success(sprintf(
            '%s %d user(s) with a cursor_color.',
            $dryRun ? 'Would update' : 'Updated',
            $updated,
        ));

        return Command::SUCCESS;
    }
}
