<?php

namespace App\Tests\Service\Post\Content;

use App\Service\Post\Content\MarkdownSerializer;
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
    // TODO: add all others
    public function test_marks(string $markType, string $expected): void
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
                                    'type' => $markType
                                ]
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
        'expected' => "Hello, world!\n\n"
    ])]
    // TODO: add all others
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

}
