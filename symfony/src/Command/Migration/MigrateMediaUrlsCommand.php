<?php

namespace App\Command\Migration;

use App\Entity\Blog;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use App\Service\Blog\MessageHandler\ReRenderPostHtmlMessageHandler;
use App\Service\Blog\UpdateBlogUrls\Updater\LegacyMediaUpdater;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessageHandler;
use App\Service\Cache\BlogCacheService;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Media was previously served from the blog's host ({blogUrl}/media/{name}).
 * It is now served from the media URL ({DOMAIN_APP}/media/{blogIdEncoded}/{name} or {MEDIA_DOMAIN}/{blogIdEncoded}/{name}).
 * This rewrites the legacy URLs saved in blog meta, posts, and users to the new format.
 *
 * Legacy URLs keep working (the blog-host /media/ route is still served), so this can be run anytime after deploying.
 */
#[AsCommand(
    name: 'blogs:migrate-media-urls',
    description: 'Rewrites legacy {blogUrl}/media/{name} URLs to the media URL format',
)]
class MigrateMediaUrlsCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $em,
        private UpdateBlogUrlsMessageHandler $handler,
        private PermalinkService $permalinkService,
        private BlogCacheService $blogCacheService,
        private ReRenderPostHtmlMessageHandler $reRenderPostHtmlHandler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not save any changes. Only reports the number of URLs to update')
            ->addOption('blog', null, InputOption::VALUE_REQUIRED, 'Only migrate the blog with this ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $onlyBlogId = $input->getOption('blog');
        $onlyBlogId = is_numeric($onlyBlogId) ? (int) $onlyBlogId : null;
        $start = microtime(true);

        $lastId = 0;
        $blogsProcessed = 0;
        $blogsUpdated = 0;
        $urlsUpdated = 0;
        /** @var array<int, string> $errors blog id => message */
        $errors = [];

        while (true) {
            $qb = $this->em->createQueryBuilder()
                ->select('b')
                ->from(Blog::class, 'b')
                ->where('b.id > :lastId')
                ->orderBy('b.id', 'ASC')
                ->setMaxResults(1)
                ->setParameter('lastId', $lastId);

            if ($onlyBlogId !== null) {
                $qb->andWhere('b.id = :id')->setParameter('id', $onlyBlogId);
            }

            /** @var Blog|null $blog */
            $blog = $qb->getQuery()->getOneOrNullResult();

            if ($blog === null) {
                break;
            }

            $id = $blog->getId();
            $lastId = $id;
            $blogsProcessed++;

            $updater = new LegacyMediaUpdater(
                $this->permalinkService->getBlogUrl($blog),
                $this->permalinkService->getMediaBlogBaseUrl($blog),
            );

            try {
                $connection = $this->em->getConnection();
                $connection->beginTransaction();

                try {
                    // links to media (ex: a PDF linked in text) are also updated
                    $this->handler->updateBlogUrls($blog, $updater, updateLinks: true);
                    $dryRun ? $connection->rollBack() : $connection->commit();
                } catch (\Throwable $e) {
                    $connection->rollBack();
                    throw $e;
                }

                $count = $updater->getUpdatedCount();

                if ($count > 0) {
                    $blogsUpdated++;
                    $urlsUpdated += $count;
                    $io->writeln(sprintf('Blog #%d (%s): %d URL(s)', $id, $blog->getSubdomain(), $count), OutputInterface::VERBOSITY_VERBOSE);

                    if (!$dryRun) {
                        // synchronously, so that the template cache is cleared after the post HTML is updated
                        ($this->reRenderPostHtmlHandler)(new ReRenderPostHtmlMessage($id));
                        $this->blogCacheService->clearTemplateCache($blog);
                    }
                }
            } catch (\Throwable $e) {
                $errors[$id] = $e->getMessage();
                $io->warning(sprintf('Blog #%d (%s) failed: %s', $id, $blog->getSubdomain(), $e->getMessage()));

                if (!$this->em->isOpen()) {
                    $io->error('The entity manager was closed. Re-run the command to continue (migrated blogs have nothing left to update).');
                    return Command::FAILURE;
                }
            }

            $this->em->clear();
        }

        $io->definitionList(
            ['Blogs processed' => $blogsProcessed],
            [$dryRun ? 'Blogs to update (dry run)' : 'Blogs updated' => $blogsUpdated],
            [$dryRun ? 'URLs to update (dry run)' : 'URLs updated' => $urlsUpdated],
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
