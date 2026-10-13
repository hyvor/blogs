<?php

namespace App\Command\Migration;

use App\Entity\Blog;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessageHandler;
use App\Service\Blog\UpdateBlogUrls\Updater\CloudinaryUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'blogs:replace-cloudinary-urls',
    description: 'Replaces Cloudinary URLs of all blogs with media.hyvor.com URLs',
)]
class ReplaceCloudinaryUrlsCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $em,
        private UpdateBlogUrlsMessageHandler $handler,
        private MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not save any changes. Reports matched known URLs and any unknown Cloudinary URLs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $start = microtime(true);

        $lastId = 0;
        $blogsProcessed = 0;
        $blogsUpdated = 0;
        $urlsUpdated = 0;
        $blogsWithUnknown = 0;
        /** @var array<int, string> $errors blog id => message */
        $errors = [];

        while (true) {
            /** @var Blog|null $blog */
            $blog = $this->em->createQueryBuilder()
                ->select('b')
                ->from(Blog::class, 'b')
                ->where('b.id > :lastId')
                ->orderBy('b.id', 'ASC')
                ->setMaxResults(1)
                ->setParameter('lastId', $lastId)
                ->getQuery()
                ->getOneOrNullResult();

            if ($blog === null) {
                break;
            }

            $id = $blog->getId();
            $lastId = $id;
            $subdomain = $blog->getSubdomain();
            $updater = new CloudinaryUpdater();
            $blogsProcessed++;

            try {
                $connection = $this->em->getConnection();
                $connection->beginTransaction();

                try {
                    $this->handler->updateBlogUrls($blog, $updater);
                    $dryRun ? $connection->rollBack() : $connection->commit();
                } catch (\Throwable $e) {
                    $connection->rollBack();
                    throw $e;
                }

                $unknown = $updater->getUnknown();
                $matched = $updater->getMatched();

                if ($matched !== [] || $unknown !== []) {
                    $label = sprintf('Blog #%d (%s)', $id, $subdomain);

                    if ($unknown === []) {
                        $io->writeln(sprintf('<fg=green>%s: %d known URL(s)</>', $label, count($matched)));
                    } else {
                        $blogsWithUnknown++;
                        $io->writeln(sprintf('<fg=yellow>%s: %d known URL(s), %d UNKNOWN Cloudinary URL(s):</>', $label, count($matched), count($unknown)));
                        foreach ($unknown as $url) {
                            $io->writeln('<fg=yellow>  ! ' . $url . '</>');
                        }
                    }

                    foreach ($matched as $old => $new) {
                        $io->writeln(sprintf('  %s -> %s', $old, $new), OutputInterface::VERBOSITY_VERBOSE);
                    }
                }

                if ($matched !== []) {
                    $blogsUpdated++;
                    $urlsUpdated += $updater->getUpdatedCount();
                    if (!$dryRun) {
                        $this->bus->dispatch(new ReRenderPostHtmlMessage($id));
                    }
                }
            } catch (\Throwable $e) {
                $errors[$id] = $e->getMessage();
                $io->warning(sprintf('Blog #%d (%s) failed: %s', $id, $subdomain, $e->getMessage()));

                if (!$this->em->isOpen()) {
                    $io->error('The entity manager was closed. Re-run the command to continue (updated blogs are skipped naturally).');
                    return Command::FAILURE;
                }
            }

            $this->em->clear();
        }

        $io->definitionList(
            ['Blogs processed' => $blogsProcessed],
            [$dryRun ? 'Blogs to update (dry run)' : 'Blogs updated' => $blogsUpdated],
            [$dryRun ? 'URLs to update (dry run)' : 'URLs updated' => $urlsUpdated],
            ['Blogs with unknown Cloudinary URLs' => $blogsWithUnknown],
            ['Errors' => count($errors)],
            ['Time taken' => sprintf('%.2fs', microtime(true) - $start)],
        );

        if ($errors !== []) {
            $io->error('Failed blogs: ' . implode(', ', array_keys($errors)));
            return Command::FAILURE;
        }

        if ($blogsWithUnknown > 0) {
            $io->warning('Some blogs still have unknown Cloudinary URLs. They were NOT replaced.');
        }

        $io->success('Done');
        return Command::SUCCESS;
    }

}
