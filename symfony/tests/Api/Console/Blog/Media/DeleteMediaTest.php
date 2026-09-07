<?php

namespace App\Tests\Api\Console\Blog\Media;

use App\Api\Console\Controller\MediaController;
use App\Entity\Enum\UserStatus;
use App\Entity\Media;
use App\Service\Media\Event\MediaDeletedEvent;
use App\Service\Media\MediaService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\UserFactory;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MediaController::class)]
#[CoversClass(MediaService::class)]
#[CoversClass(MediaDeletedEvent::class)]
class DeleteMediaTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::getContainer()->set(Filesystem::class, new Filesystem(new InMemoryFilesystemAdapter()));
    }

    public function test_deletes(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-media']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $media = MediaFactory::createOne(['blog' => $blog]);
        $mediaId = $media->getId();

        $this->consoleBlogApi('DELETE', $blog, '/media/' . $mediaId, user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->getEm()->getRepository(Media::class)->find($mediaId));

        $this->getEd()->assertDispatched(MediaDeletedEvent::class);
    }

    public function test_cannot_delete_other_blogs_media(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-media-blog-1']);
        $otherBlog = BlogFactory::createOne(['subdomain' => 'delete-media-blog-2']);
        $otherUser = UserFactory::createOne(['blog' => $otherBlog, 'status' => UserStatus::ACTIVE]);
        $media = MediaFactory::createOne(['blog' => $blog]);
        $mediaId = $media->getId();

        $this->consoleBlogApi('DELETE', $otherBlog, '/media/' . $mediaId, user: $otherUser);

        $this->assertResponseStatusCodeSame(404);
        $this->assertNotNull($this->getEm()->getRepository(Media::class)->find($mediaId));
    }
}
