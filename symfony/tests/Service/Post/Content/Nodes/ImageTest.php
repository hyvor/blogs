<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Image\Image;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use App\Service\Route\PermalinkService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Image::class)]
class ImageTest extends KernelTestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
        static::getContainer()->set(Filesystem::class, $this->filesystem);
    }

    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function postSchema(): PostSchema
    {
        return $this->getService(PostSchema::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    private function storeFile(int $blogId, string $name, string $content): void
    {
        $this->filesystem->write('blog/' . $blogId . '/' . $name, $content);
    }

    /**
     * @param positive-int $width
     * @param positive-int $height
     */
    private function pngContent(int $width, int $height = 100): string
    {
        $image = new \Imagick();
        $image->newImage($width, $height, new \ImagickPixel('white'));
        $image->setImageFormat('png');
        $content = $image->getImageBlob();
        $image->destroy();
        return $content;
    }

    public function test_json_to_html(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => 'https://example.com/img.jpg',
                        'alt' => 'An image',
                        'width' => 800,
                        'height' => 600,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<img src="https://example.com/img.jpg" loading="lazy" alt="An image" width="800" height="600">', $html);
    }

    public function test_json_to_html_without_optional_attrs(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => ['src' => 'https://example.com/img.jpg'],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<img src="https://example.com/img.jpg" loading="lazy">', $html);
    }

    public function test_json_to_html_with_figure(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => ['src' => 'https://example.com/image.png', 'alt' => 'ALT'],
                        ],
                        [
                            'type' => 'figcaption',
                            'content' => [['type' => 'text', 'text' => 'Caption']],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertSame(
            '<figure><img src="https://example.com/image.png" loading="lazy" alt="ALT"><figcaption>Caption</figcaption></figure>',
            $html
        );
    }

    public function test_adds_srcset_for_images_in_media(): void
    {
        $blog = BlogFactory::createOne();
        $permalinkService = $this->getService(PermalinkService::class);

        $name = 'image.png';
        MediaFactory::createOne([
            'blog' => $blog,
            'name' => $name,
            'original_name' => $name,
            'extension' => 'png',
        ]);
        $this->storeFile($blog->getId(), $name, $this->pngContent(2000));

        $src = $permalinkService->getBlogUrl($blog) . '/media/' . $name;
        $alt = 'ALT';
        $width = 100;
        $height = 200;

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => $src,
                        'alt' => $alt,
                        'width' => $width,
                        'height' => $height,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $blog);

        $this->assertSame(
            "<img src=\"$src\" loading=\"lazy\" alt=\"$alt\" width=\"$width\" height=\"$height\" srcset=\"$src 2000w, $src/500w 500w, $src/750w 750w, $src/1000w 1000w, $src/1500w 1500w\">",
            $html
        );
    }

    public function test_doesnt_add_larger_widths_to_srcset(): void
    {
        $blog = BlogFactory::createOne();
        $permalinkService = $this->getService(PermalinkService::class);

        $name = 'image.png';
        MediaFactory::createOne([
            'blog' => $blog,
            'name' => $name,
            'original_name' => $name,
            'extension' => 'png',
        ]);
        $this->storeFile($blog->getId(), $name, $this->pngContent(850));

        $src = $permalinkService->getBlogUrl($blog) . '/media/' . $name;
        $alt = 'ALT';
        $width = 100;
        $height = 200;

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => $src,
                        'alt' => $alt,
                        'width' => $width,
                        'height' => $height,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $blog);

        $this->assertSame(
            "<img src=\"$src\" loading=\"lazy\" alt=\"$alt\" width=\"$width\" height=\"$height\" srcset=\"$src 850w, $src/500w 500w, $src/750w 750w\">",
            $html
        );
    }

    public function test_html_to_json(): void
    {
        $html = '<figure><img src="https://example.com/img.jpg" alt="Alt" width="800" height="600"></figure>';
        $json = $this->postSchema()->documentFromHtml($html)->toJson();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/img.jpg',
                                'alt' => 'Alt',
                                'width' => '800',
                                'height' => '600',
                                'suggestions' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }

    public function test_html_to_json_without_optional_attributes(): void
    {
        $src = 'https://example.com/image.png';
        $html = "<figure><img src=\"$src\"></figure>";

        $json = $this->postSchema()->documentFromHtml($html)->toJson();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => $src,
                                'alt' => null,
                                'width' => null,
                                'height' => null,
                                'suggestions' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
