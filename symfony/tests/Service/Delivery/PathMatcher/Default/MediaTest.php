<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default;

use App\Service\Delivery\Dto\CacheControl;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\MediaService;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\MediaProcessor;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(MediaProcessor::class)]
#[CoversClass(MediaService::class)]
class MediaTest extends KernelTestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
        $mediaService = new MediaService($this->getEm(), $this->filesystem);
        static::getContainer()->set(MediaService::class, $mediaService);
    }

    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    private function storeFile(int $blogId, string $name, string $content): void
    {
        $this->filesystem->write('blog/' . $blogId . '/' . $name, $content);
    }

    public function test_matches_media(): void
    {
        $fileName = 'test.svg';
        $content = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';

        $blog = BlogFactory::createOne();
        MediaFactory::createOne([
            'blog' => $blog,
            'name' => $fileName,
            'original_name' => $fileName,
            'extension' => 'svg',
        ]);
        $this->storeFile($blog->getId(), $fileName, $content);

        $response = $this->pathMatcher()->match($blog, "/media/$fileName");

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame($content, $response->content);
        $this->assertSame('image/svg+xml', $response->mimeType);
        $this->assertSame(DeliveryFileType::MEDIA, $response->fileType);
        $this->assertSame(CacheControl::ONE_YEAR, $response->cacheControl);
    }

    public function test_gets_mime_type_from_the_file_name(): void
    {
        $blog = BlogFactory::createOne();
        MediaFactory::createOne([
            'blog' => $blog,
            'name' => 'image-without-ext.svg',
            'original_name' => 'image.svg',
            'extension' => null,
        ]);
        $this->storeFile($blog->getId(), 'image-without-ext.svg', '<svg/>');

        $response = $this->pathMatcher()->match($blog, '/media/image-without-ext.svg');

        $this->assertSame('image/svg+xml', $response->mimeType);
    }

    public function test_converts_jpg_to_webp(): void
    {
        $blog = BlogFactory::createOne();
        $jpgContent = (string)file_get_contents(__DIR__ . '/test.jpg');
        $webpContent = (string)file_get_contents(__DIR__ . '/test.webp');

        MediaFactory::createOne([
            'blog' => $blog,
            'name' => 'photo.jpg',
            'original_name' => 'photo.jpg',
            'extension' => 'jpg',
        ]);
        $this->storeFile($blog->getId(), 'photo.jpg', $jpgContent);

        $response = $this->pathMatcher()->match($blog, '/media/photo.jpg');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame($webpContent, $response->content);
        $this->assertSame('image/webp', $response->mimeType);
        $this->assertSame(DeliveryFileType::MEDIA, $response->fileType);
    }

    public function test_converts_png_to_webp(): void
    {
        $blog = BlogFactory::createOne();
        $pngContent = (string)file_get_contents(__DIR__ . '/test.png');
        $webpContent = (string)file_get_contents(__DIR__ . '/test.webp');

        MediaFactory::createOne([
            'blog' => $blog,
            'name' => 'photo.png',
            'original_name' => 'photo.png',
            'extension' => 'png',
        ]);
        $this->storeFile($blog->getId(), 'photo.png', $pngContent);

        $response = $this->pathMatcher()->match($blog, '/media/photo.png');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame($webpContent, $response->content);
        $this->assertSame('image/webp', $response->mimeType);
        $this->assertSame(DeliveryFileType::MEDIA, $response->fileType);
    }
}
