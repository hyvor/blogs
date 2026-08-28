<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\OrderedList;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OrderedList::class)]
class OrderedListTest extends KernelTestCase
{
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
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'ordered_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [['type' => 'text', 'text' => 'Item 1']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<ol><li><p>Item 1</p></li></ol>', $html);
    }

    public function test_html_to_json(): void
    {
        $json = $this->postSchema()->documentFromHtml('<ol><li>Item 1</li></ol>')->toJson();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'ordered_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'attrs' => ['suggestions' => null],
                                    'content' => [['type' => 'text', 'text' => 'Item 1']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
