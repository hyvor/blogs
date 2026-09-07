<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes\Embed;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Embed\Embed;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Embed::class)]
class EmbedTest extends KernelTestCase
{
    private const string URL = 'https://www.youtube.com/watch?v=Z0kGAz6HYM8';

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

    public function test_json_to_html(): void
    {
        $json = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'embed',
                    'attrs' => ['url' => self::URL],
                ],
            ],
        ];

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertStringStartsWith('<x-embed data-url="' . self::URL . '">', $html);
        $this->assertStringContainsString('<iframe', $html);
        $this->assertStringEndsWith('</x-embed>', $html);
    }

    public function test_handles_when_url_is_null(): void
    {
        $json = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'embed',
                    'attrs' => ['url' => null],
                ],
            ],
        ];

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertSame('', $html);
    }

    public function test_html_to_json(): void
    {
        $html = '<x-embed data-url="' . self::URL . '"></x-embed>';
        $json = $this->postSchema()->documentFromHtml($html)->toJson();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'attrs' => ['suggestions' => null],
                    'content' => [
                        [
                            'type' => 'embed',
                            'attrs' => ['url' => self::URL, 'suggestions' => null],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
