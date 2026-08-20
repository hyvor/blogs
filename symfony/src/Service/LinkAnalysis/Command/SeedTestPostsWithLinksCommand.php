<?php

namespace App\Service\LinkAnalysis\Command;

use App\Entity\Blog;
use App\Tests\Factory\PostFactory;
use App\Tests\Helper\PostContentGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\When;

#[AsCommand(
    name: 'link-analysis:seed-test-posts',
    description: 'Seed posts with links, for testing link analysis performance',
)]
#[When(env: 'dev')]
class SeedTestPostsWithLinksCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('posts', null, InputOption::VALUE_REQUIRED, 'Number of posts to seed', 100)
            ->addOption('links', null, InputOption::VALUE_REQUIRED, 'Number of links per post', 50);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $postsCount = $this->getIntOption($input, 'posts');
        $linksPerPost = $this->getIntOption($input, 'links');

        $blog = $this->em->createQueryBuilder()
            ->select('b')
            ->from(Blog::class, 'b')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$blog instanceof Blog) {
            $io->error('No blog found');
            return Command::FAILURE;
        }

        $io->writeln(sprintf(
            'Seeding %d posts with %d links each for blog: %s',
            $postsCount,
            $linksPerPost,
            $blog->getSubdomain()
        ));

        $io->progressStart($postsCount);

        for ($i = 0; $i < $postsCount; $i++) {
            PostFactory::createPublishedOneForWithVariants($blog, [], [
                'content' => $this->getContent($linksPerPost),
            ]);

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(sprintf('Seeded %d posts with %d links each.', $postsCount, $linksPerPost));

        return Command::SUCCESS;
    }

    private function getIntOption(InputInterface $input, string $name): int
    {
        $value = $input->getOption($name);
        return is_numeric($value) ? (int)$value : 0;
    }

    private function getContent(int $linksCount): string
    {
        $faker = \Faker\Factory::create();

        $links = [];
        for ($i = 0; $i < $linksCount; $i++) {
            $links[] = $faker->url();
        }

        return PostContentGenerator::withLinks($links);
    }
}
