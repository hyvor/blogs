<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content;

use App\Entity\Blog;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostContentService::class)]
class WhitespaceTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
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
