<?php

namespace App\Command\Dev;

use App\Entity\Blog;
use App\Entity\Enum\AiMessageRole;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use App\Entity\PostVariant;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\AiMessageThinkingFactory;
use App\Tests\Factory\AiMessageToolCallFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use Doctrine\DBAL\Connection;
use Hyvor\Internal\Sudo\SudoUserService;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dev:seed',
    description: 'Seed the database for development',
)]
class DevSeedCommand
{
    public function __construct(
        private Connection $connection,
        private SudoUserService $sudoUserService
    ) {}

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        Application $application
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->sudoUserService->create(1, 'sudo');

        $blogConfigs = [
            ['subdomain' => 'test', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['subdomain' => 'custom', 'hosting_at' => BlogHostingAt::DOMAIN, 'custom_domain' => CustomDomainFactory::createOne()],
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
                // ['role' => UserRole::ADMIN, 'hyvor_user_id' => 1, 'status' => UserStatus::ACTIVE],
                ['role' => UserRole::ADMIN, 'hyvor_user_id' => 1, 'status' => UserStatus::ACTIVE],
            ];
            foreach ($userConfigs as $userConfig) {
                $user = UserFactory::createOne(array_merge(['blog' => $blog], $userConfig));
                UserVariantFactory::createOne(['user' => $user, 'language' => $english]);
                UserVariantFactory::createOne(['user' => $user, 'language' => $french]);
                $users[] = $user;
            }

            $posts = [];
            $englishPostVariants = [];
            for ($i = 0; $i < 10; $i++) {
                $post = PostFactory::createOne(['blog' => $blog, 'is_page' => $i % 2 === 0]);
                $englishPostVariants[] = PostVariantFactory::createOne(['post' => $post, 'language' => $english, 'status' => $postStatuses[$i % 3]]);
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

            if ($blog->getSubdomain() === 'test') {
                $this->seedAiConversations($blog, $englishPostVariants);
            }

            $io->writeln(sprintf('Blog "%s" seeded.', $blog->getSubdomain()));
        }

        $io->success('Database seeded successfully.');

        // sync themes
        $application->doRun(new ArrayInput([
            'command' => 'themes:sync',
            // '--no-preview-blogs' => true,
        ]), $output);

        return Command::SUCCESS;
    }

    /**
     * @param PostVariant[] $postVariants
     */
    private function seedAiConversations(Blog $blog, array $postVariants): void
    {
        $postVariantId = $postVariants[0]->getId();

        // conversation 1: rewriting an intro + browsing tags
        $conversation1 = AiConversationFactory::createOne([
            'blog' => $blog,
            'title' => 'Punch up the introduction',
        ]);

        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'role' => AiMessageRole::USER,
            'content' => "Can you make the introduction of post #$postVariantId punchier?",
        ]);

        $message2 = AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'role' => AiMessageRole::ASSISTANT,
            'content' => "I've rewritten the introduction to be punchier and more engaging.",
        ]);
        AiMessageThinkingFactory::createOne([
            'ai_message' => $message2,
            'summary' => 'I should fetch the current document first to see the intro paragraph before rewriting it.',
        ]);
        AiMessageToolCallFactory::createOne([
            'ai_message' => $message2,
            'tool_name' => 'document_get',
            'arguments' => ['postVariantId' => $postVariantId],
        ]);
        AiMessageToolCallFactory::createOne([
            'ai_message' => $message2,
            'tool_name' => 'document_text_replace',
            'arguments' => [
                'postVariantId' => $postVariantId,
                'nodeId' => 'n1',
                'search' => 'This post is about our new feature.',
                'replace' => "You've been waiting for this. Here's what's new.",
                'limit' => 1,
            ],
        ]);

        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'role' => AiMessageRole::USER,
            'content' => 'Thanks! Can you also list the tags on my blog so I can pick a few for this post?',
        ]);

        $message4 = AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'role' => AiMessageRole::ASSISTANT,
            'content' => "Here are your existing tags: Product, Announcements, Tutorials, Engineering. I'd suggest 'Announcements' and 'Product' for this post.",
        ]);
        AiMessageThinkingFactory::createOne([
            'ai_message' => $message4,
            'summary' => 'Let me query the existing tags on this blog before suggesting any.',
        ]);
        AiMessageToolCallFactory::createOne([
            'ai_message' => $message4,
            'tool_name' => 'get_tags',
            'arguments' => ['limit' => 10],
        ]);

        // conversation 2: finding drafts + checking authors
        $conversation2 = AiConversationFactory::createOne([
            'blog' => $blog,
            'title' => 'Draft posts overview',
        ]);

        AiMessageFactory::createOne([
            'conversation' => $conversation2,
            'role' => AiMessageRole::USER,
            'content' => 'Which of my posts are still drafts?',
        ]);

        $message6 = AiMessageFactory::createOne([
            'conversation' => $conversation2,
            'role' => AiMessageRole::ASSISTANT,
            'content' => 'You have a few posts still in draft status. Want me to check who wrote each one?',
        ]);
        AiMessageThinkingFactory::createOne([
            'ai_message' => $message6,
            'summary' => "I'll query the post variants filtered by draft status.",
        ]);
        AiMessageToolCallFactory::createOne([
            'ai_message' => $message6,
            'tool_name' => 'get_post_variants',
            'arguments' => ['status' => 'draft'],
        ]);

        AiMessageFactory::createOne([
            'conversation' => $conversation2,
            'role' => AiMessageRole::USER,
            'content' => 'Yes, can you check who the author is for each of those?',
        ]);

        $message8 = AiMessageFactory::createOne([
            'conversation' => $conversation2,
            'role' => AiMessageRole::ASSISTANT,
            'content' => "Here's the author breakdown for your draft posts.",
        ]);
        AiMessageThinkingFactory::createOne([
            'ai_message' => $message8,
            'summary' => 'I need to fetch the list of authors on this blog to match them up with the drafts.',
        ]);
        AiMessageToolCallFactory::createOne([
            'ai_message' => $message8,
            'tool_name' => 'get_authors',
            'arguments' => [],
        ]);
    }
}
