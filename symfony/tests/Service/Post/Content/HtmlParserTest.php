<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content;

use App\Entity\Blog;
use App\Service\Post\Content\HtmlParser;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HtmlParser::class)]
class HtmlParserTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    public function test_converts_p_a_img_to_img(): void
    {
        $html = <<<HTML
            <p>
                <a href="https://example.com">
                    <img src="https://example.com/image.jpg" alt="Example Image">
                </a>
            </p>
        HTML;

        $parser = new HtmlParser($html, $this->service());
        $doc = $parser->parse($this->blog());

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/image.jpg',
                                'alt' => 'Example Image',
                                'width' => null,
                                'height' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ], $doc->toArray());
    }

    public function test_converts_p_a_img_to_img_keeps_text(): void
    {
        $html = <<<HTML
            <p>
                <a href="https://example.com">
                    <img src="https://example.com/image.jpg" alt="Example Image">
                </a> <strong>More text</strong></p>
        HTML;

        $parser = new HtmlParser($html, $this->service());
        $doc = $parser->parse($this->blog());

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/image.jpg',
                                'alt' => 'Example Image',
                                'width' => null,
                                'height' => null,
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => ' '],
                        [
                            'type' => 'text',
                            'text' => 'More text',
                            'marks' => [['type' => 'strong']],
                        ],
                    ],
                ],
            ],
        ], $doc->toArray());
    }

    public function test_removes_unnecessary_whitespaces(): void
    {
        $html = '<figure>
<img src="https://example.com/image.png" alt="Image" />
<figcaption>
<p><span>Illustrations</span></p>
</figcaption>
</figure>';

        $doc = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/image.png',
                                'alt' => 'Image',
                                'width' => null,
                                'height' => null,
                            ],
                        ],
                        [
                            'type' => 'figcaption',
                            'content' => [
                                ['type' => 'text', 'text' => 'Illustrations'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $doc);
    }
}
