<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Figure;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Figure::class)]
class FigureTest extends KernelTestCase
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
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => ['src' => 'https://example.com/img.jpg'],
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
        $this->assertSame('<figure><img src="https://example.com/img.jpg" loading="lazy"><figcaption>Caption</figcaption></figure>', $html);
    }

    public function test_html_to_json(): void
    {
        $html = '<figure><img src="https://example.com/img.jpg" alt="Alt"><figcaption>Caption</figcaption></figure>';
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
                                'width' => null,
                                'height' => null,
                            ],
                        ],
                        [
                            'type' => 'figcaption',
                            'content' => [['type' => 'text', 'text' => 'Caption']],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
