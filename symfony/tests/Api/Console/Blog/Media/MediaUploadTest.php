<?php

namespace App\Tests\Api\Console\Blog\Media;

use App\Api\Console\Controller\MediaController;
use App\Api\Console\Object\MediaObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Service\Billing\UsageService;
use App\Service\Media\Event\MediaCreatedEvent;
use App\Service\Media\MediaService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(MediaController::class)]
#[CoversClass(MediaService::class)]
#[CoversClass(MediaObjectFactory::class)]
#[CoversClass(UsageService::class)]
class MediaUploadTest extends ApiTestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
        static::getContainer()->set(Filesystem::class, $this->filesystem);
    }

    private function enableBilling(int $orgId): void
    {
        $license = BlogsLicense::trial();
        $license->storage = 1000;
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [$orgId => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)],
        );
    }

    private function fakeImage(string $originalName = 'image.png'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'media_upload_test');
        file_put_contents($path, 'fake-image-content');
        return new UploadedFile($path, $originalName, 'image/png', test: true);
    }

    public function test_uploads(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'media-upload', 'organization_id' => 1001]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->enableBilling(1001);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/media',
            ['name' => 'image.png'],
            user: $user,
            files: ['file' => $this->fakeImage()],
        );

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('id', $json);

        $this->getEd()->assertDispatched(MediaCreatedEvent::class);
        $this->assertTrue($this->filesystem->fileExists('blog/' . $blog->getId() . '/image.png'));
    }

    public function test_uploads_with_duplicate_name(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'media-upload-dup', 'organization_id' => 1002]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->enableBilling(1002);
        $this->filesystem->write('blog/' . $blog->getId() . '/image.png', 'content');

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/media',
            ['name' => 'image.png'],
            user: $user,
            files: ['file' => $this->fakeImage()],
        );

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('image-1.png', $json['name']);
        $this->assertTrue($this->filesystem->fileExists('blog/' . $blog->getId() . '/image.png'));
    }

    public function test_converts_to_kebab_case(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'media-upload-kebab', 'organization_id' => 1003]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->enableBilling(1003);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/media',
            ['name' => 'My Image.png'],
            user: $user,
            files: ['file' => $this->fakeImage()],
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame('my-image.png', $this->getJson()['name']);
    }

    public function test_uploads_with_post_id(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'media-upload-post', 'organization_id' => 1004]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->enableBilling(1004);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/media',
            ['post_id' => 2],
            user: $user,
            files: ['file' => $this->fakeImage()],
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame(2, $this->getJson()['post_id']);
    }

    public function test_throws_error_when_storage_limit_exceeded(): void
    {
        $blog = BlogFactory::createOne([
            'subdomain' => 'media-upload-limit',
            'organization_id' => 1005,
            'counts' => ['media' => 10 ** 9 * 2],
        ]);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $this->enableBilling(1005);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/media',
            [],
            user: $user,
            files: ['file' => $this->fakeImage()],
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'Total storage limit exceeded. Please upgrade your plan.',
            (string)$this->client->getResponse()->getContent(),
        );
    }
}
