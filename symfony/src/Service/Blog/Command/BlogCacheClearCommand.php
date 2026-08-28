<?php

namespace App\Service\Blog\Command;

use App\Service\Blog\BlogService;
use App\Service\Cache\BlogCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    'app:blog:clear-cache',
    description: 'Clears the cache for a specific blog',
)]
class BlogCacheClearCommand
{

    public function __construct(
        private BlogCacheService $blogCacheService,
        private EntityManagerInterface $em,
        private BlogService $blogService
    ) {}

    public function __invoke(
        OutputInterface $output,
        #[Argument] ?string $subdomain = null,
        #[Argument] ?int $id = null,
        #[Option] bool $all = false,
    ): int
    {
        if ($all) {
            $output->writeln('<info>Clearing cache for all blogs</info>');

            $query = $this->em->createQuery('SELECT b FROM App\Entity\Blog b');
            $batchSize = 100;
            $offset = 0;

            while (true) {
                $query->setFirstResult($offset);
                $query->setMaxResults($batchSize);
                $blogs = $query->getResult();
                if (empty($blogs)) {
                    break;
                }
                foreach ($blogs as $blog) {
                    $this->blogCacheService->clearAllCache($blog);
                }
                $offset += $batchSize;
                $output->writeln("<info>Cleared cache for batch of $batchSize blogs, offset $offset</info>");
            }

            return Command::SUCCESS;
        }

        if (!$subdomain && !$id) {
            $output->writeln('<error>You must provide either a subdomain or an id</error>');
            return Command::FAILURE;
        }

        $blog = null;
        if ($subdomain) {
            $blog = $this->blogService->getBlogBySubdomain($subdomain);
        } elseif ($id) {
            $blog = $this->blogService->getBlogById($id);
        }

        if (!$blog) {
            $output->writeln('<error>Blog not found</error>');
            return Command::FAILURE;
        }

        $this->blogCacheService->clearAllCache($blog);
        $output->writeln("<info>Cleared cache for blog. Subdomain: {$blog->getSubdomain()}, ID: {$blog->getId()}</info>");

        return Command::SUCCESS;
    }

}
