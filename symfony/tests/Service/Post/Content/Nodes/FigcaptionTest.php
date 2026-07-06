<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Figcaption;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Figcaption::class)]
class FigcaptionTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    public function test_json_to_html_standalone(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                ['type' => 'figcaption'],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<figcaption></figcaption>', $html);
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
                            'content' => [['type' => 'text', 'text' => 'A caption']],
                        ],
                    ],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertStringContainsString('<figcaption>A caption</figcaption>', $html);
    }

    public function test_html_to_json(): void
    {
        $html = '<figure><img src="https://example.com/img.jpg"><figcaption>A caption</figcaption></figure>';
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
                                'alt' => null,
                                'width' => null,
                                'height' => null,
                            ],
                        ],
                        [
                            'type' => 'figcaption',
                            'content' => [
                                ['type' => 'text', 'text' => 'A caption'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
