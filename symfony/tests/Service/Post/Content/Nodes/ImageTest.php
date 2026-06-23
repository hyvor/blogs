<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Image\Image;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Image::class)]
class ImageTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
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
        ]);

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
        ]);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<img src="https://example.com/img.jpg" loading="lazy">', $html);
    }

    public function test_html_to_json(): void
    {
        $html = '<figure><img src="https://example.com/img.jpg" alt="Alt" width="800" height="600"></figure>';
        $json = $this->service()->getJsonFromHtml($html, $this->blog());

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
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
