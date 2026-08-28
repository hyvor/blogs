<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\ListItem;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListItem::class)]
class ListItemTest extends KernelTestCase
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
        $content = 'I am a list item';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'list_item',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertSame("<li>$content</li>", $html);
    }

    public function test_html_to_json(): void
    {
        $content = 'A list item';
        $html = "<li>$content</li>";

        $json = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bullet_list',
                    'attrs' => ['suggestions' => null],
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'attrs' => ['suggestions' => null],
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => $content,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
