<?php

namespace App\Tests\Api\Console\Blog\Media;

use App\Api\Console\Controller\MediaController;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\UserStatus;
use App\Service\App\MessageTransport;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlEvent;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlLock;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessage;
use App\Service\Media\MediaService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(MediaController::class)]
#[CoversClass(MediaService::class)]
#[CoversClass(UpdateBlogUrlLock::class)]
class UpdateMediaTest extends ApiTestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
        static::getContainer()->set(Filesystem::class, $this->filesystem);
    }

    public function test_updates_name_and_moves_file(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-media', 'hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->filesystem->write('blog/' . $blog->getId() . '/test.png', 'content');
        $media = MediaFactory::createOne(['blog' => $blog, 'name' => 'test.png']);

        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'content' => json_encode([
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'image',
                                'attrs' => [
                                    'src' => 'https://update-media.hyvorblogs.io/media/' . $media->getName(),
                                    'alt' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/media/' . $media->getId(), [
            'name' => 'new-name.png',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('new-name.png', $this->getJson()['name']);

        $this->getEm()->refresh($media);
        $this->assertSame('new-name.png', $media->getName());

        $this->assertFalse($this->filesystem->fileExists('blog/' . $blog->getId() . '/test.png'));
        $this->assertTrue($this->filesystem->fileExists('blog/' . $blog->getId() . '/new-name.png'));

        $transport = $this->transport(MessageTransport::ASYNC)->throwExceptions();
        $transport->queue()->assertContains(UpdateBlogUrlsMessage::class);
        $message = $transport->queue()->messages(UpdateBlogUrlsMessage::class)[0];

        $this->assertSame(UpdateBlogUrlEvent::MEDIA_URL_CHANGED, $message->event);
        $this->assertSame($blog->getId(), $message->blogId);
        $this->assertSame('https://update-media.hyvorblogs.io/media/test.png', $message->mediaOldUrl);
        $this->assertSame('https://update-media.hyvorblogs.io/media/new-name.png', $message->mediaNewUrl);
        $this->assertCount(2, $message->lockKeys);

        $transport->processOrFail();

        refresh($variant);
        $contentJson = json_decode($variant->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('https://update-media.hyvorblogs.io/media/new-name.png', $contentJson['content'][0]['content'][0]['attrs']['src']);
    }

    public function test_updates_to_kebab_case(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-media-kebab']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->filesystem->write('blog/' . $blog->getId() . '/test.png', 'content');
        $media = MediaFactory::createOne(['blog' => $blog, 'name' => 'test.png']);

        $this->consoleBlogApi('PATCH', $blog, '/media/' . $media->getId(), [
            'name' => 'New Name.png',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('new-name.png', $this->getJson()['name']);
    }

    public function test_handles_duplicates(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-media-dup']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->filesystem->write('blog/' . $blog->getId() . '/test.png', 'content');
        $this->filesystem->write('blog/' . $blog->getId() . '/new-name.png', 'content');
        $media = MediaFactory::createOne(['blog' => $blog, 'name' => 'test.png']);

        $this->consoleBlogApi('PATCH', $blog, '/media/' . $media->getId(), [
            'name' => 'new-name.png',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('new-name-1.png', $this->getJson()['name']);

        $this->assertFalse($this->filesystem->fileExists('blog/' . $blog->getId() . '/test.png'));
        $this->assertTrue($this->filesystem->fileExists('blog/' . $blog->getId() . '/new-name.png'));
        $this->assertTrue($this->filesystem->fileExists('blog/' . $blog->getId() . '/new-name-1.png'));
    }

    public function test_rejects_slashes_in_name(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-media-slash']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $media = MediaFactory::createOne(['blog' => $blog, 'name' => 'test.png']);

        $this->consoleBlogApi('PATCH', $blog, '/media/' . $media->getId(), [
            'name' => 'foo/bar.png',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }
}
