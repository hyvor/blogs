<?php

namespace App\Command\Dev;

use App\Entity\AiConversation;
use App\Entity\Blog;
use App\Entity\Enum\AiMessageEventDocumentChangeStatus;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\Enum\AiMessageRole;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use App\Entity\PostVariant;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageEventFactory;
use App\Tests\Factory\AiMessageFactory;
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
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Zenstruck\Foundry\Persistence\save;

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
        Application $application,
        #[Option('themes')] bool $withThemes = false
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

        if ($withThemes) {
            // sync themes
            $application->doRun(new ArrayInput([
                'command' => 'themes:sync',
                // '--no-preview-blogs' => true,
            ]), $output);
        }

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

        $this->addUserMessage($conversation1, "Can you make the introduction of post #$postVariantId punchier?");

        $message2 = AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'role' => AiMessageRole::ASSISTANT,
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message2,
            'type' => AiMessageEventType::THINKING,
            'content' => 'I should fetch the current document first to see the intro paragraph before rewriting it.',
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message2,
            'type' => AiMessageEventType::DOCUMENT_CHANGE,
            'content' => null,
            'post_variant' => $postVariants[0],
            'document_content' => $this->docJson(
                "You've been waiting for this. Here's what's new.",
            ),
            // already reviewed and applied - matches the post's current (unchanged) version
            'document_change_status' => AiMessageEventDocumentChangeStatus::REVIEWED,
            'document_change_ops_count' => 1,
            'post_variant_version' => $postVariants[0]->getContentUnsavedVersion(),
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message2,
            'type' => AiMessageEventType::TEXT,
            'content' => "I've rewritten the introduction to be punchier and more engaging.",
        ]);

        $this->addUserMessage($conversation1, 'Thanks! Can you also list the tags on my blog so I can pick a few for this post?');

        $message4 = AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'role' => AiMessageRole::ASSISTANT,
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message4,
            'type' => AiMessageEventType::THINKING,
            'content' => 'Let me query the existing tags on this blog before suggesting any.',
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message4,
            'type' => AiMessageEventType::QUERY,
            'content' => null,
            'tool_name' => 'get_tags',
            'tool_input' => ['limit' => 10],
            'tool_output' => [
                ['id' => 1, 'is_private' => false, 'slug' => 'product', 'name' => 'Product', 'description' => null, 'posts_count' => 4],
                ['id' => 2, 'is_private' => false, 'slug' => 'announcements', 'name' => 'Announcements', 'description' => null, 'posts_count' => 2],
                ['id' => 3, 'is_private' => false, 'slug' => 'tutorials', 'name' => 'Tutorials', 'description' => null, 'posts_count' => 6],
                ['id' => 4, 'is_private' => false, 'slug' => 'engineering', 'name' => 'Engineering', 'description' => null, 'posts_count' => 3],
            ],
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message4,
            'type' => AiMessageEventType::TEXT,
            'content' => "Here are your existing tags: Product, Announcements, Tutorials, Engineering. I'd suggest 'Announcements' and 'Product' for this post.",
        ]);

        // conversation 2: finding drafts + checking authors
        $conversation2 = AiConversationFactory::createOne([
            'blog' => $blog,
            'title' => 'Draft posts overview',
        ]);

        $this->addUserMessage($conversation2, 'Which of my posts are still drafts?');

        $message6 = AiMessageFactory::createOne([
            'conversation' => $conversation2,
            'role' => AiMessageRole::ASSISTANT,
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message6,
            'type' => AiMessageEventType::THINKING,
            'content' => "I'll query the post variants filtered by draft status.",
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message6,
            'type' => AiMessageEventType::QUERY,
            'content' => null,
            'tool_name' => 'get_post_variants',
            'tool_input' => ['languageCode' => 'en', 'status' => 'draft'],
            'tool_output' => [
                ['id' => $postVariants[0]->getId(), 'slug' => $postVariants[0]->getSlug(), 'status' => 'draft', 'title' => $postVariants[0]->getTitle(), 'description' => null],
            ],
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message6,
            'type' => AiMessageEventType::TEXT,
            'content' => 'You have a few posts still in draft status. Want me to check who wrote each one?',
        ]);

        $this->addUserMessage($conversation2, 'Yes, can you check who the author is for each of those?');

        $message8 = AiMessageFactory::createOne([
            'conversation' => $conversation2,
            'role' => AiMessageRole::ASSISTANT,
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message8,
            'type' => AiMessageEventType::THINKING,
            'content' => 'I need to fetch the list of authors on this blog to match them up with the drafts.',
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message8,
            'type' => AiMessageEventType::QUERY,
            'content' => null,
            'tool_name' => 'get_authors',
            'tool_input' => ['limit' => 50, 'offset' => 0, 'search' => null],
            'tool_output' => [
                ['id' => 1, 'slug' => 'admin', 'name' => 'Admin', 'posts_count' => count($postVariants)],
            ],
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message8,
            'type' => AiMessageEventType::TEXT,
            'content' => "Here's the author breakdown for your draft posts.",
        ]);

        // conversation 3: finding a published post + rewriting its closing paragraph
        $publishedVariant = $this->firstPublishedVariant($postVariants);

        $conversation3 = AiConversationFactory::createOne([
            'blog' => $blog,
            'title' => 'Improve the closing paragraph',
        ]);

        $this->addUserMessage(
            $conversation3,
            'Find one of my published posts and rewrite its closing paragraph to end on a stronger note.'
        );

        $message10 = AiMessageFactory::createOne([
            'conversation' => $conversation3,
            'role' => AiMessageRole::ASSISTANT,
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message10,
            'type' => AiMessageEventType::THINKING,
            'content' => 'Let me look for a published post first.',
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message10,
            'type' => AiMessageEventType::QUERY,
            'content' => null,
            'tool_name' => 'get_post_variants',
            'tool_input' => ['languageCode' => 'en', 'status' => 'published'],
            'tool_output' => [
                ['id' => $publishedVariant->getId(), 'slug' => $publishedVariant->getSlug(), 'status' => 'published', 'title' => $publishedVariant->getTitle(), 'description' => null],
            ],
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message10,
            'type' => AiMessageEventType::THINKING,
            'content' => "Found it - now let me rewrite the closing paragraph to end on a stronger note.",
        ]);
        // capture the version the agent "fetched" before the post is edited again below, so
        // the diff review UI has a stale document_change to test against
        $fetchedPostVariantVersion = $publishedVariant->getContentUnsavedVersion();

        AiMessageEventFactory::createOne([
            'ai_message' => $message10,
            'type' => AiMessageEventType::DOCUMENT_CHANGE,
            'content' => null,
            'post_variant' => $publishedVariant,
            'document_content' => $this->docJson(
                'This post is about our new feature.',
                "Give it a try today, and let us know what you think - we can't wait to hear from you.",
            ),
            // still needs review, and (see below) the post has since been edited again -
            // exercises the DiffReviewModal's stale-version warning
            'document_change_status' => AiMessageEventDocumentChangeStatus::PENDING,
            'document_change_ops_count' => 2,
            'post_variant_version' => $fetchedPostVariantVersion,
        ]);
        AiMessageEventFactory::createOne([
            'ai_message' => $message10,
            'type' => AiMessageEventType::TEXT,
            'content' => "I found your published post \"{$publishedVariant->getTitle()}\" and rewrote its closing paragraph to end on a stronger note.",
        ]);

        // simulate the post being edited again after the agent suggested the change above,
        // so its live content_unsaved_version is now ahead of what the document_change recorded
        $publishedVariant->setContentUnsaved($this->docJson(
            'This post is about our new feature. It now has a slightly different intro too.',
            "Give it a try today, and let us know what you think - we can't wait to hear from you.",
        ));
        $publishedVariant->setContentUnsavedVersion($fetchedPostVariantVersion + 1);
        save($publishedVariant);
    }

    private function addUserMessage(AiConversation $conversation, string $content): void
    {
        $message = AiMessageFactory::createOne([
            'conversation' => $conversation,
            'role' => AiMessageRole::USER,
        ]);

        AiMessageEventFactory::createOne([
            'ai_message' => $message,
            'type' => AiMessageEventType::TEXT,
            'content' => $content,
        ]);
    }

    /**
     * @param PostVariant[] $postVariants
     */
    private function firstPublishedVariant(array $postVariants): PostVariant
    {
        foreach ($postVariants as $postVariant) {
            if ($postVariant->getStatus() === PostVariantStatus::PUBLISHED) {
                return $postVariant;
            }
        }

        throw new \RuntimeException('No published post variant found to seed an AI document_change event with.');
    }

    private function docJson(string ...$paragraphs): string
    {
        return (string) json_encode([
            'type' => 'doc',
            'content' => array_map(
                fn (string $text) => [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $text]],
                ],
                $paragraphs,
            ),
        ]);
    }
}
