<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Media;

use App\Service\Delivery\Dto\CacheControl;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\MediaProcessor;
use App\Service\Media\MediaService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(MediaProcessor::class)]
#[CoversClass(MediaService::class)]
class MediaTest extends KernelTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    private function storeFile(int $blogId, string $name, string $content): void
    {
        $this->getService(Filesystem::class)->write('blog/' . $blogId . '/' . $name, $content);
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
        $this->assertSame('image/webp', $response->mimeType);
        $this->assertSame(DeliveryFileType::MEDIA, $response->fileType);
    }

    public function test_converts_png_to_webp(): void
    {
        $blog = BlogFactory::createOne();
        $pngContent = (string)file_get_contents(__DIR__ . '/test.png');

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
        $this->assertSame('image/webp', $response->mimeType);
        $this->assertSame(DeliveryFileType::MEDIA, $response->fileType);
    }

    /**
     * prefix with test_ to run this test.
     * it is here to check how long it takes to convert a large image to webp.
     * 2026-07-12: around 3s without resizing
     */
    public function /*test_*/converts_large_image(): void
    {
        if (!file_exists(__DIR__ . '/test-large.jpg')) {
            $content = file_get_contents('https://images.unsplash.com/photo-1782760794099-dc5a50bd55a6?ixlib=rb-4.1.0&q=85&fm=jpg&crop=entropy&cs=srgb&dl=max-bohme-cBiQfqb1BQU-unsplash.jpg');
            $this->assertSame(
                4894632,
                strlen($content),
            );
            file_put_contents(
        __DIR__ . '/test-large.jpg',
                $content
            );
        }


        $jpg = (string)file_get_contents(__DIR__ . '/test-large.jpg');

        $blog = BlogFactory::createOne();
        MediaFactory::createOne([
            'blog' => $blog,
            'name' => 'large.jpg',
            'original_name' => 'large.jpg',
            'extension' => 'jpg',
        ]);
        $this->storeFile($blog->getId(), 'large.jpg', $jpg);

        $startTime = microtime(true);
        $response = $this->pathMatcher()->match($blog, '/media/large.jpg');
        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame('image/webp', $response->mimeType);
        dd('processing took: ' . microtime(true) - $startTime . ' seconds');
    }
}
