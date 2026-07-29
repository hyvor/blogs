<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Blockquote;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Blockquote::class)]
class BlockquoteTest extends KernelTestCase
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
        $content = 'I am a blockquote';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
                    'content' => [
                        ['type' => 'text', 'text' => $content],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertSame("<blockquote>$content</blockquote>", $html);
    }

    public function test_html_to_json(): void
    {
        $content = 'A blockquote';
        $json = $this->service()->getJsonFromHtml("<blockquote>$content</blockquote>", $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => $content],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
