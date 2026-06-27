<?php

namespace App\Command\Dev;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dev:seed',
    description: 'Seed the database for development',
)]
class DevSeedCommand extends Command
{
    public function __construct(
        private Connection $connection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $blogConfigs = [
            ['subdomain' => 'test', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['subdomain' => 'custom', 'hosting_at' => BlogHostingAt::SELF, 'hosting_domain' => 'hyvorblogscustom.test'],
            ['subdomain' => 'self', 'hosting_at' => BlogHostingAt::SELF, 'hosting_url' => 'https://blogs.hyvor.test/blog'],
            ['subdomain' => 'dev', 'type' => BlogType::DEV, 'hosting_at' => BlogHostingAt::SELF, 'hosting_url' => 'http://127.0.0.1:8885'],
        ];

        $postStatuses = [PostVariantStatus::DRAFT, PostVariantStatus::PUBLISHED, PostVariantStatus::SCHEDULED];

        foreach ($blogConfigs as $blogConfig) {
            $blog = BlogFactory::createOne(array_merge(['organization_id' => 1], $blogConfig));

            $english = LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'name' => 'English', 'is_primary' => true]);
            $french = LanguageFactory::createOne(['blog' => $blog, 'code' => 'fr', 'name' => 'French', 'is_primary' => false]);

            BlogVariantFactory::createOne(['blog' => $blog, 'language' => $english]);
            BlogVariantFactory::createOne(['blog' => $blog, 'language' => $french]);

            $tags = [];
            for ($i = 0; $i < 10; $i++) {
                $tag = TagFactory::createOne(['blog' => $blog]);
                TagVariantFactory::createOne(['tag' => $tag, 'language' => $english]);
                TagVariantFactory::createOne(['tag' => $tag, 'language' => $french]);
                $tags[] = $tag;
            }

            $users = [];
            $userConfigs = [
                // ['role' => UserRole::OWNER, 'hyvor_user_id' => 1, 'status' => UserStatus::ACTIVE],
                ['role' => UserRole::ADMIN, 'hyvor_user_id' => 1, 'status' => UserStatus::ACTIVE],
            ];
            foreach ($userConfigs as $userConfig) {
                $user = UserFactory::createOne(array_merge(['blog' => $blog], $userConfig));
                UserVariantFactory::createOne(['user' => $user, 'language' => $english]);
                UserVariantFactory::createOne(['user' => $user, 'language' => $french]);
                $users[] = $user;
            }

            $posts = [];
            for ($i = 0; $i < 10; $i++) {
                $post = PostFactory::createOne(['blog' => $blog, 'is_page' => $i % 2 === 0]);
                PostVariantFactory::createOne(['post' => $post, 'language' => $english, 'status' => $postStatuses[$i % 3]]);
                PostVariantFactory::createOne(['post' => $post, 'language' => $french, 'status' => $postStatuses[($i + 1) % 3]]);
                $posts[] = $post;
            }

            foreach ($posts as $post) {
                $shuffled = $tags;
                shuffle($shuffled);
                foreach (array_slice($shuffled, 0, 3) as $tag) {
                    $this->connection->insert('post_tag', ['post_id' => $post->getId(), 'tag_id' => $tag->getId()]);
                }
                foreach ($users as $user) {
                    $this->connection->insert('post_author', ['post_id' => $post->getId(), 'user_id' => $user->getId()]);
                }
            }

            RouteFactory::createDefaultsFor($blog);

            $io->writeln(sprintf('Blog "%s" seeded.', $blog->getSubdomain()));
        }

        $io->success('Database seeded successfully.');

        return Command::SUCCESS;
    }
}
