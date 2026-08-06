<?php

namespace App\Tests\Service\Post\Content;

use App\Service\Post\Content\Markdown\MarkdownSerializationOptions;
use App\Service\Post\Content\Markdown\MarkdownSerializer;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(MarkdownSerializer::class)]
class MarkdownSerializerTest extends KernelTestCase
{

    private function convertToMarkdown(array $doc): string
    {
        $doc = $this->getService(PostContentService::class)->getDocumentFromJson($doc);
        return trim(new MarkdownSerializer()->serialize($doc));
    }

    public function test_basics(): void
    {

        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type'    => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello, world!'
                        ]
                    ]
                ],
                [
                    'type'   => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Bold',
                            'marks' => [
                                [
                                    'type' => 'strong'
                                ]
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => ' and ',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'italic',
                            'marks' => [
                                [
                                    'type' => 'em'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mardown = $this->convertToMarkdown($doc);

        $expected = <<<MD
        Hello, world!

        **Bold** and _italic_
        MD;

        $this->assertSame($expected, $mardown);

    }

    #[TestWith(['strong', '**test**'])]
    #[TestWith(['em', '_test_'])]
    #[TestWith(['code', '`test`'])]
    #[TestWith(['strike', '~~test~~'])]
    #[TestWith(['sub', '~test~'])]
    #[TestWith(['sup', '^test^'])]
    #[TestWith(['highlight', '==test=='])]
    #[TestWith(['link', '[test](https://example.com)', ['href' => 'https://example.com']])]
    public function test_marks(string $markType, string $expected, array $markAttrs = []): void
    {
        $mark = ['type' => $markType];
        if ($markAttrs) {
            $mark['attrs'] = $markAttrs;
        }

        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type'    => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'test',
                            'marks' => [
                                $mark
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mardown = $this->convertToMarkdown($doc);

        $this->assertSame($expected, $mardown);
    }

    public function test_multiple_marks(): void
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type'    => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'test',
                            'marks' => [
                                [
                                    'type' => 'strong'
                                ],
                                [
                                    'type' => 'em'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mardown = $this->convertToMarkdown($doc);

        $this->assertSame('**_test_**', $mardown);
    }

    #[TestWith([
        'type' => 'paragraph',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Hello, world!'
            ]
        ],
        'expected' => 'Hello, world!'
    ])]
    #[TestWith([
        'type' => 'heading',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Heading text'
            ]
        ],
        'expected' => '## Heading text',
        'attrs' => ['level' => 2]
    ])]
    #[TestWith([
        'type' => 'code_block',
        'content' => [
            [
                'type' => 'text',
                'text' => 'echo 1;'
            ]
        ],
        'expected' => "```php\necho 1;\n```",
        'attrs' => ['language' => 'php']
    ])]
    #[TestWith([
        'type' => 'callout',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Note text'
            ]
        ],
        'expected' => "> [💡, fg=#000000, bg=#f1f1ef]\n> Note text",
        'attrs' => ['emoji' => '💡', 'fg' => '#000000', 'bg' => '#f1f1ef']
    ])]
    #[TestWith([
        'type' => 'bullet_list',
        'content' => [
            [
                'type' => 'list_item',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 1']]]
                ]
            ],
            [
                'type' => 'list_item',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 2']]]
                ]
            ]
        ],
        'expected' => "- Item 1\n- Item 2"
    ])]
    #[TestWith([
        'type' => 'ordered_list',
        'content' => [
            [
                'type' => 'list_item',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 1']]]
                ]
            ],
            [
                'type' => 'list_item',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 2']]]
                ]
            ]
        ],
        'expected' => "1. Item 1\n2. Item 2"
    ])]
    #[TestWith([
        'type' => 'figure',
        'content' => [
            [
                'type' => 'image',
                'attrs' => ['src' => 'https://example.com/image.png', 'alt' => 'An image', 'width' => 100, 'height' => 200]
            ]
        ],
        'expected' => '![An image](https://example.com/image.png =100x200)'
    ])]
    #[TestWith([
        'type' => 'figure',
        'content' => [
            [
                'type' => 'embed',
                'attrs' => ['url' => 'https://example.com/embed']
            ]
        ],
        'expected' => '[#embed](https://example.com/embed)'
    ])]
    #[TestWith([
        'type' => 'audio',
        'expected' => '[#audio](https://example.com/audio.mp3)',
        'attrs' => ['src' => 'https://example.com/audio.mp3']
    ])]
    #[TestWith([
        'type' => 'bookmark',
        'expected' => '[#bookmark](https://example.com)',
        'attrs' => ['url' => 'https://example.com']
    ])]
    #[TestWith([
        'type' => 'button',
        'content' => [
            ['type' => 'text', 'text' => 'Click me']
        ],
        'expected' => '[#button](https://example.com)',
        'attrs' => ['href' => 'https://example.com']
    ])]
    #[TestWith([
        'type' => 'horizontal_rule',
        'expected' => '---'
    ])]
    #[TestWith([
        'type' => 'toc',
        'expected' => '[#toc]'
    ])]
    #[TestWith([
        'type' => 'custom_html',
        'content' => [
            ['type' => 'text', 'text' => '<div>Custom</div>']
        ],
        'expected' => '<div>Custom</div>'
    ])]
    public function test_nodes(
        string $type,
        string $expected,
        array $content = [],
        array $attrs = []
    ): void
    {

        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type'    => $type,
                    'attrs' => $attrs,
                    'content' => $content
                ]
            ]
        ];

        $mardown = $this->convertToMarkdown($doc);

        $this->assertSame($expected, $mardown);

    }

    public function test_paragraphs_within_blockquote(): void
    {

        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'This is a blockquote.'
                                ]
                            ]
                        ],
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'It has multiple paragraphs.'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mardown = $this->convertToMarkdown($doc);

        $expected = <<<MD
        > This is a blockquote.
        >
        > It has multiple paragraphs.
        MD;

        $this->assertSame($expected, $mardown);
    }

    public function test_nested_lists(): void
    {

        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bullet_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 1']]],
                                [
                                    'type' => 'ordered_list',
                                    'content' => [
                                        [
                                            'type' => 'list_item',
                                            'content' => [
                                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Subitem 1']]]
                                            ]
                                        ],
                                        [
                                            'type' => 'list_item',
                                            'content' => [
                                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Subitem 2']]]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 2']]]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mardown = $this->convertToMarkdown($doc);

        $expected = <<<MD
        - Item 1
          1. Subitem 1
          2. Subitem 2
        - Item 2
        MD;

        $this->assertSame($expected, $mardown);
    }

    public function test_hard_break(): void
    {

        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type'    => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Line 1'],
                        ['type' => 'hard_break'],
                        ['type' => 'text', 'text' => 'Line 2']
                    ]
                ]
            ]
        ];

        $mardown = $this->convertToMarkdown($doc);

        $expected = <<<MD
        Line 1
        Line 2
        MD;

        $this->assertSame($expected, $mardown);
    }

    public function test_table(): void
    {

        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'table',
                    'content' => [
                        [
                            'type' => 'table_row',
                            'content' => [
                                [
                                    'type'    => 'table_header',
                                    'content' => [
                                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Header 1']]]
                                    ]
                                ],
                                [
                                    'type'    => 'table_header',
                                    'content' => [
                                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Header 2']]]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'table_row',
                            'content' => [
                                [
                                    'type'    => 'table_cell',
                                    'content' => [
                                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Cell 1']]]
                                    ]
                                ],
                                [
                                    'type'    => 'table_cell',
                                    'content' => [
                                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Cell 2']]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mardown = $this->convertToMarkdown($doc);

        $expected = <<<MD
        | Header 1 | Header 2 |
        | --- | --- |
        | Cell 1 | Cell 2 |
        MD;

        $this->assertSame($expected, $mardown);
    }

    public function test_with_nodeid_map(): void
    {

        $docJson = [
            'type' => 'doc',
            'content' => [
                [
                    'type'    => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello, world!']
                    ]
                ],
                [
                    'type'    => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'This is a test.']
                    ]
                ]
            ]
        ];

        $doc = $this->getService(PostContentService::class)->getDocumentFromJson($docJson);

        $nodeIdMap = [
            ['node' => $doc->content->first(), 'id' => 'p-1']
        ];

        $options = new MarkdownSerializationOptions($nodeIdMap);

        $serializer = new MarkdownSerializer();
        $mardown = trim($serializer->serialize($doc, $options));

        $expected = <<<MD
        #[p-1] Hello, world!

        This is a test.
        MD;

        $this->assertSame($expected, $mardown);
    }

    public function test_nodeid_map_with_nested_nodes(): void
    {
        $docJson = [
            'type' => 'doc',
            'content' => [
                [
                    'type'    => 'blockquote',
                    'content' => [
                        [
                            'type'    => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => 'This is a blockquote.']
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'ordered_list',
                    'content' => [
                        [
                            'type'    => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 1']]]
                            ]
                        ],
                        [
                            'type'    => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 2']]]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $doc = $this->getService(PostContentService::class)->getDocumentFromJson($docJson);

        $nodeIdMap = [
            ['node' => $doc->content->first(), 'id' => 'quote-1'],
            ['node' => $doc->content->first()->content->first(), 'id' => 'p-1'],
            ['node' => $doc->content->last(), 'id' => 'list-1'],
            ['node' => $doc->content->last()->content->first(), 'id' => 'list-item-1'],
            ['node' => $doc->content->last()->content->last(), 'id' => 'list-item-2'],
            ['node' => $doc->content->last()->content->first()->content->first(), 'id' => 'p-2'],
            ['node' => $doc->content->last()->content->last()->content->first(), 'id' => 'p-3'],
        ];

        $options = new MarkdownSerializationOptions($nodeIdMap);

        $serializer = new MarkdownSerializer();
        $mardown = trim($serializer->serialize($doc, $options));

        $expected = <<<MD
        #[quote-1] > #[p-1] This is a blockquote.

        #[list-1] 1. #[list-item-1] #[p-2] Item 1
        2. #[list-item-2] #[p-3] Item 2
        MD;

        $this->assertSame($expected, $mardown);
    }

}
