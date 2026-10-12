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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'blogs:replace-cloudinary-urls',
    description: 'Replaces Cloudinary URLs of all blogs with media.hyvor.com URLs',
)]
class ReplaceCloudinaryUrlsCommand extends Command
{

    private const int BATCH_SIZE = 50;

    public function __construct(
        private EntityManagerInterface $em,
        private UpdateBlogUrlsMessageHandler $handler,
        private MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $start = microtime(true);

        $lastId = 0;
        $blogsProcessed = 0;
        $blogsUpdated = 0;
        $urlsUpdated = 0;
        /** @var array<int, string> $errors blog id => message */
        $errors = [];

        while (true) {
            /** @var int[] $ids */
            $ids = $this->em->createQueryBuilder()
                ->select('b.id')
                ->from(Blog::class, 'b')
                ->where('b.id > :lastId')
                ->orderBy('b.id', 'ASC')
                ->setMaxResults(self::BATCH_SIZE)
                ->setParameter('lastId', $lastId)
                ->getQuery()
                ->getSingleColumnResult();

            if ($ids === []) {
                break;
            }

            foreach ($ids as $id) {
                $blogsProcessed++;
                $blog = $this->em->find(Blog::class, $id);

                if ($blog === null) {
                    continue;
                }

                $subdomain = $blog->getSubdomain();
                $updater = new CloudinaryUpdater();

                try {
                    $this->handler->updateBlogUrls($blog, $updater);

                    if ($updater->getUpdatedCount() > 0) {
                        $blogsUpdated++;
                        $urlsUpdated += $updater->getUpdatedCount();
                        $this->bus->dispatch(new ReRenderPostHtmlMessage($id));
                        $io->writeln(sprintf('Blog #%d (%s): %d URL(s) updated', $id, $subdomain, $updater->getUpdatedCount()), OutputInterface::VERBOSITY_VERBOSE);

                        foreach ($updater->getMatched() as $old => $new) {
                            $io->writeln(sprintf('  %s -> %s', $old, $new), OutputInterface::VERBOSITY_VERBOSE);
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

            $lastId = end($ids);
        }

        $io->definitionList(
            ['Blogs processed' => $blogsProcessed],
            ['Blogs updated' => $blogsUpdated],
            ['URLs updated' => $urlsUpdated],
            ['Errors' => count($errors)],
            ['Time taken' => sprintf('%.2fs', microtime(true) - $start)],
        );

        if ($errors !== []) {
            $io->error('Failed blogs: ' . implode(', ', array_keys($errors)));
            return Command::FAILURE;
        }

        $io->success('Done');
        return Command::SUCCESS;
    }

}
