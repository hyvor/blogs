<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Table\Table;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Table::class)]
class TableTest extends KernelTestCase
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
                    'type' => 'table',
                    'content' => [
                        [
                            'type' => 'table_row',
                            'content' => [
                                [
                                    'type' => 'table_cell',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'content' => [['type' => 'text', 'text' => 'Cell 1']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<div class="table-container"><table><tr><td><p>Cell 1</p></td></tr></table></div>', $html);
    }

    public function test_html_to_json(): void
    {
        $html = '<table><tr><td>Cell 1</td></tr></table>';
        $json = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'table',
                    'content' => [
                        [
                            'type' => 'table_row',
                            'content' => [
                                [
                                    'type' => 'table_cell',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'content' => [['type' => 'text', 'text' => 'Cell 1']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
