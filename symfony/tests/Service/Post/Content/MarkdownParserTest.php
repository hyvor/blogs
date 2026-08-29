<?php

namespace App\Tests\Service\Post\Content;

use App\Service\Post\Content\Markdown\MarkdownParser;
use App\Service\Post\Content\Markdown\MarkdownSerializer;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Phrosemirror\Document\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(MarkdownParser::class)]
class MarkdownParserTest extends KernelTestCase
{

    /**
     * @return list<array<string, mixed>>
     */
    private function parse(string $markdown): array
    {
        $nodes = new MarkdownParser()->parse($markdown);
        return array_values(array_map(static fn (Node $node): array => $node->toArray(), $nodes));
    }

    public function test_basics(): void
    {
        $markdown = <<<MD
        Hello, world!

        **Bold** and _italic_
        MD;

        $nodes = $this->parse($markdown);

        $this->assertSame([
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
        ], $nodes);
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
        $nodes = $this->parse($markdown);

        $this->assertSame([
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => $text, 'marks' => [['type' => $markType]]],
                ],
            ],
        ], $nodes);
    }

    public function test_link_mark(): void
    {
        $nodes = $this->parse('[test](https://example.com)');

        $this->assertSame([
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
        ], $nodes);
    }

    public function test_multiple_marks(): void
    {
        $nodes = $this->parse('**_test_**');

        $this->assertSame([
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
        ], $nodes);
    }

    public function test_heading(): void
    {
        $nodes = $this->parse('## Heading text');

        $this->assertSame([
            [
                'type' => 'heading',
                'attrs' => ['level' => 2],
                'content' => [['type' => 'text', 'text' => 'Heading text']],
            ],
        ], $nodes);
    }

    public function test_heading_with_id(): void
    {
        $nodes = $this->parse('## Heading text {#my-id}');

        $this->assertSame([
            [
                'type' => 'heading',
                'attrs' => ['level' => 2, 'id' => 'my-id'],
                'content' => [['type' => 'text', 'text' => 'Heading text']],
            ],
        ], $nodes);
    }

    public function test_heading_that_is_only_an_id(): void
    {
        $nodes = $this->parse('## {#my-id}');

        $this->assertSame([
            [
                'type' => 'heading',
                'attrs' => ['level' => 2, 'id' => 'my-id'],
                'content' => [],
            ],
        ], $nodes);
    }

    public function test_code_block(): void
    {
        $markdown = <<<MD
        ```php
        echo 1;
        ```
        MD;

        $nodes = $this->parse($markdown);

        $this->assertSame([
            [
                'type' => 'code_block',
                'attrs' => ['language' => 'php'],
                'content' => [['type' => 'text', 'text' => 'echo 1;']],
            ],
        ], $nodes);
    }

    public function test_callout(): void
    {
        $markdown = "> [💡, fg=#000000, bg=#f1f1ef]\n> Note text";

        $nodes = $this->parse($markdown);

        $this->assertSame([
            [
                'type' => 'callout',
                'attrs' => ['emoji' => '💡', 'fg' => '#000000', 'bg' => '#f1f1ef'],
                'content' => [['type' => 'text', 'text' => 'Note text']],
            ],
        ], $nodes);
    }

    public function test_bullet_list(): void
    {
        $markdown = <<<MD
        - Item 1
        - Item 2
        MD;

        $nodes = $this->parse($markdown);

        $this->assertSame([
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
        ], $nodes);
    }

    public function test_ordered_list(): void
    {
        $markdown = <<<MD
        1. Item 1
        2. Item 2
        MD;

        $nodes = $this->parse($markdown);

        $this->assertSame([
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
        ], $nodes);
    }

    public function test_nested_lists(): void
    {
        $markdown = <<<MD
        - Item 1
          1. Subitem 1
          2. Subitem 2
        - Item 2
        MD;

        $nodes = $this->parse($markdown);

        $this->assertSame([
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
        ], $nodes);
    }

    public function test_hard_break(): void
    {
        $markdown = "Line 1\nLine 2";

        $nodes = $this->parse($markdown);

        $this->assertSame([
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Line 1'],
                    ['type' => 'hard_break'],
                    ['type' => 'text', 'text' => 'Line 2'],
                ],
            ],
        ], $nodes);
    }

    public function test_blockquote_with_multiple_paragraphs(): void
    {
        $markdown = <<<MD
        > This is a blockquote.
        >
        > It has multiple paragraphs.
        MD;

        $nodes = $this->parse($markdown);

        $this->assertSame([
            [
                'type' => 'blockquote',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This is a blockquote.']]],
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'It has multiple paragraphs.']]],
                ],
            ],
        ], $nodes);
    }

    public function test_table(): void
    {
        $markdown = <<<MD
        | Header 1 | Header 2 |
        | --- | --- |
        | Cell 1 | Cell 2 |
        MD;

        $nodes = $this->parse($markdown);

        $this->assertSame([
            [
                'type' => 'table',
                'content' => [
                    [
                        'type' => 'table_row',
                        'content' => [
                            [
                                'type' => 'table_header',
                                'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Header 1']]]],
                            ],
                            [
                                'type' => 'table_header',
                                'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Header 2']]]],
                            ],
                        ],
                    ],
                    [
                        'type' => 'table_row',
                        'content' => [
                            [
                                'type' => 'table_cell',
                                'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Cell 1']]]],
                            ],
                            [
                                'type' => 'table_cell',
                                'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Cell 2']]]],
                            ],
                        ],
                    ],
                ],
            ],
        ], $nodes);
    }

    public function test_image_with_size(): void
    {
        $nodes = $this->parse('![An image](https://example.com/image.png "100x200")');

        $this->assertSame([
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
        ], $nodes);
    }

    public function test_image_with_only_width(): void
    {
        $nodes = $this->parse('![An image](https://example.com/image.png "100x")');

        $this->assertSame([
            [
                'type' => 'figure',
                'content' => [
                    [
                        'type' => 'image',
                        'attrs' => [
                            'src' => 'https://example.com/image.png',
                            'alt' => 'An image',
                            'width' => 100,
                        ],
                    ],
                ],
            ],
        ], $nodes);
    }

    public function test_image_without_size(): void
    {
        $nodes = $this->parse('![An image](https://example.com/image.png)');

        $this->assertSame([
            [
                'type' => 'figure',
                'content' => [
                    [
                        'type' => 'image',
                        'attrs' => [
                            'src' => 'https://example.com/image.png',
                            'alt' => 'An image',
                        ],
                    ],
                ],
            ],
        ], $nodes);
    }

    public function test_embed(): void
    {
        $nodes = $this->parse('![#embed](https://example.com/embed)');

        $this->assertSame([
            [
                'type' => 'figure',
                'content' => [
                    ['type' => 'embed', 'attrs' => ['url' => 'https://example.com/embed']],
                ],
            ],
        ], $nodes);
    }

    public function test_button(): void
    {
        $nodes = $this->parse('![#button "Click me"](https://example.com)');

        $this->assertSame([
            [
                'type' => 'button',
                'attrs' => ['href' => 'https://example.com'],
                'content' => [['type' => 'text', 'text' => 'Click me']],
            ],
        ], $nodes);
    }

    public function test_button_with_quotes_and_brackets_in_text(): void
    {
        $nodes = $this->parse('![#button "Say "hi" \\[now\\]"](https://example.com)');

        $this->assertSame([
            [
                'type' => 'button',
                'attrs' => ['href' => 'https://example.com'],
                'content' => [['type' => 'text', 'text' => 'Say "hi" [now]']],
            ],
        ], $nodes);
    }

    public function test_button_with_marks_in_text(): void
    {
        $nodes = $this->parse('![#button "Click **me**"](https://example.com)');

        $this->assertSame([
            [
                'type' => 'button',
                'attrs' => ['href' => 'https://example.com'],
                'content' => [
                    ['type' => 'text', 'text' => 'Click '],
                    ['type' => 'text', 'text' => 'me', 'marks' => [['type' => 'strong']]],
                ],
            ],
        ], $nodes);
    }

    public function test_button_that_is_entirely_marked(): void
    {
        $nodes = $this->parse('![#button "**Click me**"](https://example.com)');

        $this->assertSame([
            [
                'type' => 'button',
                'attrs' => ['href' => 'https://example.com'],
                'content' => [
                    ['type' => 'text', 'text' => 'Click me', 'marks' => [['type' => 'strong']]],
                ],
            ],
        ], $nodes);
    }

    public function test_button_with_empty_text(): void
    {
        $nodes = $this->parse('![#button ""](https://example.com)');

        $this->assertSame([
            [
                'type' => 'button',
                'attrs' => ['href' => 'https://example.com'],
                'content' => [['type' => 'text', 'text' => '']],
            ],
        ], $nodes);
    }

    public function test_audio(): void
    {
        $nodes = $this->parse('![#audio](https://example.com/audio.mp3)');

        $this->assertSame([
            ['type' => 'audio', 'attrs' => ['src' => 'https://example.com/audio.mp3']],
        ], $nodes);
    }

    public function test_bookmark(): void
    {
        $nodes = $this->parse('![#bookmark](https://example.com)');

        $this->assertSame([
            ['type' => 'bookmark', 'attrs' => ['url' => 'https://example.com']],
        ], $nodes);
    }

    public function test_horizontal_rule(): void
    {
        $nodes = $this->parse('---');

        $this->assertSame([
            ['type' => 'horizontal_rule'],
        ], $nodes);
    }

    public function test_toc(): void
    {
        $nodes = $this->parse('![#toc]()');

        $this->assertSame([
            ['type' => 'toc'],
        ], $nodes);
    }

    public function test_custom_html(): void
    {
        $nodes = $this->parse('<div>Custom</div>');

        $this->assertSame([
            [
                'type' => 'custom_html',
                'content' => [['type' => 'text', 'text' => '<div>Custom</div>']],
            ],
        ], $nodes);
    }

    public function test_empty_markdown_produces_no_nodes(): void
    {
        $this->assertSame([], $this->parse(''));
    }

    public function test_round_trip_via_serializer(): void
    {
        $docJson = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2, 'id' => 'title'],
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
                [
                    'type' => 'button',
                    'attrs' => ['href' => 'https://example.com/action'],
                    'content' => [
                        ['type' => 'text', 'text' => 'Click '],
                        ['type' => 'text', 'text' => 'me', 'marks' => [['type' => 'strong']]],
                    ],
                ],
            ],
        ];

        $postContentService = $this->getService(PostContentService::class);
        $doc = $postContentService->getDocumentFromJson($docJson);

        $serializer = new MarkdownSerializer();
        $markdown = $serializer->serialize($doc);

        $reparsed = $this->parse($markdown);

        $this->assertSame($docJson['content'], $reparsed);
    }

}
