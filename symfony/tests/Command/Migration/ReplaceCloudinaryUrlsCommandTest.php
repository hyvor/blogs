<?php

namespace App\Tests\Command\Migration;

use App\Command\Migration\ReplaceCloudinaryUrlsCommand;
use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\User;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(ReplaceCloudinaryUrlsCommand::class)]
class ReplaceCloudinaryUrlsCommandTest extends KernelTestCase
{

    private const string OLD = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images/author-images/1.webp';
    private const string NEW = 'https://media.hyvor.com/blogs.hyvor.com/filler-images/author-images/1.webp';

    public function test_replaces_urls_and_dispatches_re_render(): void
    {
        $blog = BlogFactory::createOne();
        $meta = clone $blog->getMeta();
        $meta->logo_url = self::OLD;
        $meta->cover_url = 'https://example.com/cover.png';
        $blog->setMeta($meta);

        $post = PostFactory::createOneFor($blog, ['featured_image_url' => self::OLD]);
        $user = UserFactory::createOne(['blog' => $blog, 'picture_url' => self::OLD]);

        // untouched blog
        $otherBlog = BlogFactory::createOne();
        $this->getEm()->flush();

        $commandTester = $this->getCommandTester('blogs:replace-cloudinary-urls');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();
        $this->assertStringContainsString('Blogs updated', $display);
        $this->assertStringContainsString('URLs updated', $display);
        $this->assertStringContainsString('Time taken', $display);

        $updatedBlog = $this->getEm()->find(Blog::class, $blog->getId());
        $this->assertNotNull($updatedBlog);
        $this->assertSame(self::NEW, $updatedBlog->getMeta()->logo_url);
        $this->assertSame('https://example.com/cover.png', $updatedBlog->getMeta()->cover_url);

        $updatedPost = $this->getEm()->find(Post::class, $post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame(self::NEW, $updatedPost->getFeaturedImageUrl());

        $updatedUser = $this->getEm()->find(User::class, $user->getId());
        $this->assertNotNull($updatedUser);
        $this->assertSame(self::NEW, $updatedUser->getPictureUrl());

        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(ReRenderPostHtmlMessage::class, 1);
        /** @var ReRenderPostHtmlMessage $message */
        $message = $dispatched->first(ReRenderPostHtmlMessage::class)->getMessage();
        $this->assertSame($blog->getId(), $message->blogId);
        $this->assertNotSame($otherBlog->getId(), $message->blogId);
    }

    public function test_no_dispatch_when_nothing_changes(): void
    {
        BlogFactory::createOne();

        $commandTester = $this->getCommandTester('blogs:replace-cloudinary-urls');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->transport('async')->dispatched()->assertCount(0);
    }

}
