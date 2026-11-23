<?php

namespace App\Tests\Command\Migration;

use App\Command\Migration\MigrateMediaUrlsCommand;
use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\User;
use App\Service\Blog\UpdateBlogUrls\Updater\LegacyMediaUpdater;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(MigrateMediaUrlsCommand::class)]
#[CoversClass(LegacyMediaUpdater::class)]
class MigrateMediaUrlsCommandTest extends KernelTestCase
{

    /**
     * Blog that was hosted at a custom domain ({subdomain}.com)
     */
    private function createBlog(string $subdomain): Blog
    {
        $blog = BlogFactory::createOne([
            'subdomain' => $subdomain,
        ]);
        CustomDomainFactory::createActiveFor($blog, $subdomain . '.com');
        return $blog;
    }

    private function newBase(Blog $blog): string
    {
        return 'https://' . $blog->getSubdomain() . '.hyvorblogs.io/media';
    }

    private function docJson(string $imageSrc, string $linkHref): string
    {
        return (string) json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => ['src' => $imageSrc],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'link',
                            'marks' => [
                                ['type' => 'link', 'attrs' => ['href' => $linkHref]],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function normalizeJson(?string $json): string
    {
        $this->assertNotNull($json);
        return (string) json_encode(json_decode($json, true), JSON_UNESCAPED_SLASHES);
    }

    public function test_migrates_custom_domain_media_urls(): void
    {
        $blog = $this->createBlog('supun');
        $language = LanguageFactory::createOneFor($blog);
        $meta = clone $blog->getMeta();
        $meta->logo_url = 'https://supun.com/media/logo.png';
        $meta->cover_url = 'https://example.com/cover.png';
        $blog->setMeta($meta);

        $post = PostFactory::createOneFor($blog, ['featured_image_url' => 'https://supun.com/media/featured.png']);
        $variant = PostVariantFactory::createOneFor($post, [
            'status' => PostVariantStatus::DRAFT,
            'content' => $this->docJson('https://supun.com/media/pic.png', 'https://supun.com/media/file.pdf'),
            'content_unsaved' => $this->docJson('https://supun.com/media/draft.png', 'https://supun.com/some-post'),
        ], $language);
        $user = UserFactory::createOne(['blog' => $blog, 'picture_url' => 'https://supun.com/media/me.png']);

        // other blogs' legacy URLs are not touched
        $otherBlog = $this->createBlog('other');
        $otherPost = PostFactory::createOneFor($otherBlog, ['featured_image_url' => 'https://supun.com/media/featured.png']);

        $this->getEm()->flush();

        $commandTester = $this->getCommandTester('blogs:migrate-media-urls');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('URLs updated', $commandTester->getDisplay());

        $base = $this->newBase($blog);

        $updatedBlog = $this->getEm()->find(Blog::class, $blog->getId());
        $this->assertNotNull($updatedBlog);
        $this->assertSame("$base/logo.png", $updatedBlog->getMeta()->logo_url);
        $this->assertSame('https://example.com/cover.png', $updatedBlog->getMeta()->cover_url);

        $updatedPost = $this->getEm()->find(Post::class, $post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame("$base/featured.png", $updatedPost->getFeaturedImageUrl());

        $updatedVariant = $this->getEm()->find(PostVariant::class, $variant->getId());
        $this->assertNotNull($updatedVariant);
        $content = $this->normalizeJson($updatedVariant->getContent());
        $this->assertStringContainsString("\"$base/pic.png\"", $content);
        $this->assertStringContainsString("\"$base/file.pdf\"", $content);
        $contentUnsaved = $this->normalizeJson($updatedVariant->getContentUnsaved());
        $this->assertStringContainsString("\"$base/draft.png\"", $contentUnsaved);
        // non-media links are not touched
        $this->assertStringContainsString('"https://supun.com/some-post"', $contentUnsaved);

        $updatedUser = $this->getEm()->find(User::class, $user->getId());
        $this->assertNotNull($updatedUser);
        $this->assertSame("$base/me.png", $updatedUser->getPictureUrl());

        $updatedOtherPost = $this->getEm()->find(Post::class, $otherPost->getId());
        $this->assertNotNull($updatedOtherPost);
        $this->assertSame('https://supun.com/media/featured.png', $updatedOtherPost->getFeaturedImageUrl());
    }

    public function test_migrates_hosting_url_media_urls(): void
    {
        $blog = BlogFactory::createOne([
            'subdomain' => 'supun',
            'hosting_url' => 'https://example.com/blog',
        ]);
        $post = PostFactory::createOneFor($blog, ['featured_image_url' => 'https://example.com/blog/media/a.png']);
        $otherPost = PostFactory::createOneFor($blog, ['featured_image_url' => 'https://example.com/media/b.png']);
        $this->getEm()->flush();

        $commandTester = $this->getCommandTester('blogs:migrate-media-urls');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $updatedPost = $this->getEm()->find(Post::class, $post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame('https://supun.hyvorblogs.io/media/a.png', $updatedPost->getFeaturedImageUrl());

        // not under the hosting URL
        $updatedOtherPost = $this->getEm()->find(Post::class, $otherPost->getId());
        $this->assertNotNull($updatedOtherPost);
        $this->assertSame('https://example.com/media/b.png', $updatedOtherPost->getFeaturedImageUrl());
    }

    public function test_skips_blogs_hosted_on_subdomain(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'supun']);
        $url = 'https://supun.hyvorblogs.io/media/a.png';
        $post = PostFactory::createOneFor($blog, ['featured_image_url' => $url]);
        $this->getEm()->flush();

        $commandTester = $this->getCommandTester('blogs:migrate-media-urls');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/Blogs processed\s+0/', $commandTester->getDisplay());

        $updatedPost = $this->getEm()->find(Post::class, $post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame($url, $updatedPost->getFeaturedImageUrl());
    }

    public function test_dry_run_changes_nothing(): void
    {
        $blog = $this->createBlog('supun');
        $post = PostFactory::createOneFor($blog, ['featured_image_url' => 'https://supun.com/media/featured.png']);
        $this->getEm()->flush();

        $commandTester = $this->getCommandTester('blogs:migrate-media-urls');
        $commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('URLs to update (dry run)', $commandTester->getDisplay());

        $updatedPost = $this->getEm()->find(Post::class, $post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame('https://supun.com/media/featured.png', $updatedPost->getFeaturedImageUrl());
    }

    public function test_only_migrates_the_given_blog(): void
    {
        $blog = $this->createBlog('supun');
        $post = PostFactory::createOneFor($blog, ['featured_image_url' => 'https://supun.com/media/a.png']);
        $otherBlog = $this->createBlog('other');
        $otherPost = PostFactory::createOneFor($otherBlog, ['featured_image_url' => 'https://other.com/media/b.png']);
        $this->getEm()->flush();

        $this->getCommandTester('blogs:migrate-media-urls')->execute(['--blog' => (string) $blog->getId()]);

        $updatedPost = $this->getEm()->find(Post::class, $post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame($this->newBase($blog) . '/a.png', $updatedPost->getFeaturedImageUrl());

        $updatedOtherPost = $this->getEm()->find(Post::class, $otherPost->getId());
        $this->assertNotNull($updatedOtherPost);
        $this->assertSame('https://other.com/media/b.png', $updatedOtherPost->getFeaturedImageUrl());
    }

    public function test_already_migrated_urls_are_left_alone(): void
    {
        $blog = $this->createBlog('supun');
        $url = $this->newBase($blog) . '/a.png';
        $post = PostFactory::createOneFor($blog, ['featured_image_url' => $url]);
        $this->getEm()->flush();

        $commandTester = $this->getCommandTester('blogs:migrate-media-urls');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/URLs updated\s+0/', $commandTester->getDisplay());
        $updatedPost = $this->getEm()->find(Post::class, $post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame($url, $updatedPost->getFeaturedImageUrl());
    }

}
