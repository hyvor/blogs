<?php

namespace App\Tests\Service\Post\Content;

use App\Service\Post\Content\Markdown\MarkdownParser;
use App\Service\Post\Content\Markdown\MarkdownSerializer;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(MarkdownParser::class)]
class MarkdownParserTest extends KernelTestCase
{

    /**
     * @return array<string, mixed>
     */
    private function parse(string $markdown): array
    {
        $parser = new MarkdownParser($this->getService(PostContentService::class));
        return $parser->parse($markdown)->toArray();
    }

    public function test_basics(): void
    {
        $markdown = <<<MD
        Hello, world!

        **Bold** and _italic_
        MD;

        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello, world!'],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Bold', 'marks' => [['type' => 'strong']]],
                        ['type' => 'text', 'text' => ' and '],
                        ['type' => 'text', 'text' => 'italic', 'marks' => [['type' => 'em']]],
                    ],
                ],
            ],
        ], $doc);
    }

    #[TestWith(['**test**', 'strong', 'test'])]
    #[TestWith(['_test_', 'em', 'test'])]
    #[TestWith(['`test`', 'code', 'test'])]
    #[TestWith(['~~test~~', 'strike', 'test'])]
    #[TestWith(['~test~', 'sub', 'test'])]
    #[TestWith(['^test^', 'sup', 'test'])]
    #[TestWith(['==test==', 'highlight', 'test'])]
    public function test_marks(string $markdown, string $markType, string $text): void
    {
        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => $text, 'marks' => [['type' => $markType]]],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_link_mark(): void
    {
        $doc = $this->parse('[test](https://example.com)');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'test',
                            'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://example.com']]],
                        ],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_multiple_marks(): void
    {
        $doc = $this->parse('**_test_**');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'test',
                            'marks' => [['type' => 'strong'], ['type' => 'em']],
                        ],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_heading(): void
    {
        $doc = $this->parse('## Heading text');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2, 'id' => null],
                    'content' => [['type' => 'text', 'text' => 'Heading text']],
                ],
            ],
        ], $doc);
    }

    public function test_code_block(): void
    {
        $markdown = <<<MD
        ```php
        echo 1;
        ```
        MD;

        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'code_block',
                    'attrs' => ['language' => 'php', 'name' => null, 'annotations' => null],
                    'content' => [['type' => 'text', 'text' => 'echo 1;']],
                ],
            ],
        ], $doc);
    }

    public function test_callout(): void
    {
        $markdown = "> [💡, fg=#000000, bg=#f1f1ef]\n> Note text";

        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'callout',
                    'attrs' => ['emoji' => '💡', 'bg' => '#f1f1ef', 'fg' => '#000000'],
                    'content' => [['type' => 'text', 'text' => 'Note text']],
                ],
            ],
        ], $doc);
    }

    public function test_bullet_list(): void
    {
        $markdown = <<<MD
        - Item 1
        - Item 2
        MD;

        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bullet_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 1']]],
                            ],
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 2']]],
                            ],
                        ],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_ordered_list(): void
    {
        $markdown = <<<MD
        1. Item 1
        2. Item 2
        MD;

        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'ordered_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 1']]],
                            ],
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 2']]],
                            ],
                        ],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_nested_lists(): void
    {
        $markdown = <<<MD
        - Item 1
          1. Subitem 1
          2. Subitem 2
        - Item 2
        MD;

        $doc = $this->parse($markdown);

        $this->assertSame([
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
                                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Subitem 1']]],
                                            ],
                                        ],
                                        [
                                            'type' => 'list_item',
                                            'content' => [
                                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Subitem 2']]],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Item 2']]],
                            ],
                        ],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_hard_break(): void
    {
        $markdown = "Line 1\nLine 2";

        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Line 1'],
                        ['type' => 'hard_break'],
                        ['type' => 'text', 'text' => 'Line 2'],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_blockquote_with_multiple_paragraphs(): void
    {
        $markdown = <<<MD
        > This is a blockquote.
        >
        > It has multiple paragraphs.
        MD;

        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
                    'content' => [
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This is a blockquote.']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'It has multiple paragraphs.']]],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_table(): void
    {
        $markdown = <<<MD
        | Header 1 | Header 2 |
        | --- | --- |
        | Cell 1 | Cell 2 |
        MD;

        $doc = $this->parse($markdown);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'table',
                    'content' => [
                        [
                            'type' => 'table_row',
                            'content' => [
                                [
                                    'type' => 'table_header',
                                    'attrs' => ['colspan' => 1, 'rowspan' => 1, 'colwidth' => null],
                                    'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Header 1']]]],
                                ],
                                [
                                    'type' => 'table_header',
                                    'attrs' => ['colspan' => 1, 'rowspan' => 1, 'colwidth' => null],
                                    'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Header 2']]]],
                                ],
                            ],
                        ],
                        [
                            'type' => 'table_row',
                            'content' => [
                                [
                                    'type' => 'table_cell',
                                    'attrs' => ['colspan' => 1, 'rowspan' => 1, 'colwidth' => null],
                                    'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Cell 1']]]],
                                ],
                                [
                                    'type' => 'table_cell',
                                    'attrs' => ['colspan' => 1, 'rowspan' => 1, 'colwidth' => null],
                                    'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Cell 2']]]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_image_with_size(): void
    {
        $doc = $this->parse('![An image](https://example.com/image.png =100x200)');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/image.png',
                                'alt' => 'An image',
                                'width' => 100,
                                'height' => 200,
                            ],
                        ],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_embed(): void
    {
        $doc = $this->parse('[#embed](https://example.com/embed)');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        ['type' => 'embed', 'attrs' => ['url' => 'https://example.com/embed']],
                    ],
                ],
            ],
        ], $doc);
    }

    public function test_audio(): void
    {
        $doc = $this->parse('[#audio](https://example.com/audio.mp3)');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                ['type' => 'audio', 'attrs' => ['src' => 'https://example.com/audio.mp3']],
            ],
        ], $doc);
    }

    public function test_bookmark(): void
    {
        $doc = $this->parse('[#bookmark](https://example.com)');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                ['type' => 'bookmark', 'attrs' => ['url' => 'https://example.com']],
            ],
        ], $doc);
    }

    public function test_horizontal_rule(): void
    {
        $doc = $this->parse('---');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                ['type' => 'horizontal_rule'],
            ],
        ], $doc);
    }

    public function test_toc(): void
    {
        $doc = $this->parse('[#toc]');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                ['type' => 'toc', 'attrs' => ['levels' => [1, 2, 3, 4, 5, 6]]],
            ],
        ], $doc);
    }

    public function test_custom_html(): void
    {
        $doc = $this->parse('<div>Custom</div>');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'custom_html',
                    'content' => [['type' => 'text', 'text' => '<div>Custom</div>']],
                ],
            ],
        ], $doc);
    }

    public function test_empty_markdown_produces_empty_paragraph(): void
    {
        $doc = $this->parse('');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph'],
            ],
        ], $doc);
    }

    public function test_round_trip_via_serializer(): void
    {
        $docJson = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2, 'id' => null],
                    'content' => [['type' => 'text', 'text' => 'Title']],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Bold', 'marks' => [['type' => 'strong']]],
                        ['type' => 'text', 'text' => ' and '],
                        ['type' => 'text', 'text' => 'a link', 'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://example.com']]]],
                    ],
                ],
            ],
        ];

        $postContentService = $this->getService(PostContentService::class);
        $doc = $postContentService->getDocumentFromJson($docJson);

        $serializer = new MarkdownSerializer();
        $markdown = $serializer->serialize($doc);

        $reparsed = $this->parse($markdown);

        $this->assertSame($docJson, $reparsed);
    }

}
