<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content;

use App\Service\Post\Content\ProsemirrorHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProsemirrorHelper::class)]
class ProsemirrorHelperTest extends TestCase
{
    private function helper(): ProsemirrorHelper
    {
        return new ProsemirrorHelper();
    }

    public function test_returns_the_array(): void
    {
        $array = ['type' => 'doc', 'content' => []];
        $obj = (object) $array;

        $this->assertSame('doc', $this->helper()->getArrayJson($array)['type']);
        $this->assertSame('doc', $this->helper()->getArrayJson($obj)['type']);
        $this->assertSame('doc', $this->helper()->getArrayJson(json_encode($array))['type']);
        // empty doc
        $this->assertSame('doc', $this->helper()->getArrayJson(null)['type']);
    }

    public function test_finds_blocks(): void
    {
        $json = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'test']],
                        ],
                        [
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'test2']],
                        ],
                    ],
                ],
            ],
        ];

        $blocks = $this->helper()->findBlocks($json, 'paragraph');

        $this->assertCount(2, $blocks);
        foreach ($blocks as $block) {
            $this->assertSame('paragraph', $block['type']);
        }
    }

    public function test_updates_blocks(): void
    {
        $json = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'test']],
                        ],
                    ],
                ],
            ],
        ];

        $data = $this->helper()->updateBlocks($json, function (array $node) {
            if ($node['type'] === 'blockquote') {
                $node['content'][] = [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'test2']],
                ];
            }
            return $node;
        });

        $this->assertCount(2, $data['content'][0]['content']);
    }

    public function test_updates_urls(): void
    {
        $oldUrl = 'https://1.com';
        $newUrl = 'https://2.com';

        $json = [
            'type' => 'doc',
            'content' => [
                ['type' => 'image', 'attrs' => ['src' => $oldUrl . '/media/image.png']],
                ['type' => 'image', 'attrs' => ['src' => $oldUrl . '/media/image2.png']],
                ['type' => 'image', 'attrs' => ['src' => 'https://anotherdomain.com/image.png']],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'marks' => [
                                [
                                    'type' => 'link',
                                    'attrs' => ['href' => $oldUrl . '/path'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->helper()->updateUrls($json, $oldUrl, $newUrl);

        $this->assertSame($newUrl . '/media/image.png', $result['content'][0]['attrs']['src']);
        $this->assertSame($newUrl . '/media/image2.png', $result['content'][1]['attrs']['src']);
        $this->assertSame('https://anotherdomain.com/image.png', $result['content'][2]['attrs']['src']);
        $this->assertSame($newUrl . '/path', $result['content'][3]['content'][0]['marks'][0]['attrs']['href']);
    }
}
