<?php

namespace App\Tests\Service\Ai\Agent\Tool\DocumentOps;

use App\Service\Ai\Agent\Tool\DocumentOps\FetchedDocument;
use App\Service\Ai\Agent\Tool\DocumentOps\OpInsert;
use App\Service\Ai\Agent\Tool\DocumentOps\OpReplace;
use App\Service\Ai\Agent\Tool\DocumentOps\OpReplaceText;
use App\Service\Ai\Agent\Tool\DocumentOps\OpsApplier;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Phrosemirror\Document\Node;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OpsApplier::class)]
class OpsApplierTest extends KernelTestCase
{

    private function basicNode(): Node
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello, world!'
                        ]
                    ]
                ]
            ]
        ];

        return $this->getService(PostContentService::class)->getDocumentFromJson($doc);
    }

    private function nestedNode(): Node
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello, world!'
                        ]
                    ]
                ],
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
                        ]
                    ]
                ]
            ]
        ];

        return $this->getService(PostContentService::class)->getDocumentFromJson($doc);
    }

    private function listNode(): Node
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Intro'
                        ]
                    ]
                ],
                [
                    'type' => 'bullet_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => 'Item one'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => 'Item two'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->getService(PostContentService::class)->getDocumentFromJson($doc);
    }

    private function markedNode(): Node
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'foo bar foo '
                        ],
                        [
                            'type' => 'text',
                            'text' => 'foo',
                            'marks' => [['type' => 'strong']]
                        ],
                        [
                            'type' => 'text',
                            'text' => ' baz foo'
                        ]
                    ]
                ]
            ]
        ];

        return $this->getService(PostContentService::class)->getDocumentFromJson($doc);
    }

    public function test_replace(): void
    {
        $doc = $this->basicNode();
        $ops = [
            new OpReplace('p-1', 'Goodbye, world!')
        ];

        $fetchedDoc = new FetchedDocument(
            $doc,
            ['p-1' => $doc->content->first()]
        );

        $opsApplier = new OpsApplier();
        $newDoc = $opsApplier->apply($fetchedDoc, $ops);

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Goodbye, world!'
                            ]
                        ]
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

    public function test_replace_with_nested_content(): void
    {
        $doc = $this->nestedNode();
        $ops = [
            new OpReplace('p-1', "This is a better blockquote.\n\nwith another paragraph.")
        ];

        $fetchedDoc = new FetchedDocument(
            $doc,
            ['p-1' => $doc->content->nth(1)->content->first()]
        );

        $opsApplier = new OpsApplier();
        $newDoc = $opsApplier->apply($fetchedDoc, $ops);

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello, world!'
                            ]
                        ]
                    ],
                    [
                        'type' => 'blockquote',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'This is a better blockquote.'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'with another paragraph.'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

    public function test_insert_after(): void
    {
        $doc = $this->basicNode();
        $ops = [
            new OpInsert('p-1', "This is a new paragraph.\n\n>and another", false)
        ];

        $fetchedDoc = new FetchedDocument(
            $doc,
            ['p-1' => $doc->content->first()]
        );

        $opsApplier = new OpsApplier();
        $newDoc = $opsApplier->apply($fetchedDoc, $ops);

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello, world!'
                            ]
                        ]
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'This is a new paragraph.'
                            ]
                        ]
                    ],
                    [
                        'type' => 'blockquote',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'and another'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

    public function test_insert_before_with_nested_content(): void
    {
        $doc = $this->nestedNode();
        $ops = [
            new OpInsert('p-2', 'A blockquote intro.', true)
        ];

        $fetchedDoc = new FetchedDocument(
            $doc,
            ['p-2' => $doc->content->nth(1)->content->first()]
        );

        $opsApplier = new OpsApplier();
        $newDoc = $opsApplier->apply($fetchedDoc, $ops);

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello, world!'
                            ]
                        ]
                    ],
                    [
                        'type' => 'blockquote',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'A blockquote intro.'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'This is a blockquote.'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

    public function test_insert_after_with_nested_content(): void
    {
        $doc = $this->nestedNode();
        $ops = [
            new OpInsert('p-2', 'A blockquote outro.', false)
        ];

        $fetchedDoc = new FetchedDocument(
            $doc,
            ['p-2' => $doc->content->nth(1)->content->first()]
        );

        $opsApplier = new OpsApplier();
        $newDoc = $opsApplier->apply($fetchedDoc, $ops);

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello, world!'
                            ]
                        ]
                    ],
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
                                        'text' => 'A blockquote outro.'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

    public function test_complex_with_nested_lists(): void
    {
        $doc = $this->listNode();

        $bulletList = $doc->content->nth(1);
        $firstItemParagraph = $bulletList->content->nth(0)->content->first();
        $secondItemParagraph = $bulletList->content->nth(1)->content->first();

        $ops = [
            new OpInsert('p-2', 'Item one, continued.', false),
            new OpReplace('p-3', "Item two A.\n\nItem two B.")
        ];

        $fetchedDoc = new FetchedDocument(
            $doc,
            [
                'p-2' => $firstItemParagraph,
                'p-3' => $secondItemParagraph,
            ]
        );

        $opsApplier = new OpsApplier();
        $newDoc = $opsApplier->apply($fetchedDoc, $ops);

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Intro'
                            ]
                        ]
                    ],
                    [
                        'type' => 'bullet_list',
                        'content' => [
                            [
                                'type' => 'list_item',
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'content' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'Item one'
                                            ]
                                        ]
                                    ],
                                    [
                                        'type' => 'paragraph',
                                        'content' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'Item one, continued.'
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'type' => 'list_item',
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'content' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'Item two A.'
                                            ]
                                        ]
                                    ],
                                    [
                                        'type' => 'paragraph',
                                        'content' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'Item two B.'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

    public function test_replace_text(): void
    {
        $doc = $this->markedNode();
        $ops = [
            new OpReplaceText('p-1', 'foo', 'qux', 2)
        ];

        $fetchedDoc = new FetchedDocument(
            $doc,
            ['p-1' => $doc->content->first()]
        );

        $opsApplier = new OpsApplier();
        $newDoc = $opsApplier->apply($fetchedDoc, $ops);

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'qux bar qux '
                            ],
                            [
                                'type' => 'text',
                                'text' => 'foo',
                                'marks' => [['type' => 'strong']]
                            ],
                            [
                                'type' => 'text',
                                'text' => ' baz foo'
                            ]
                        ]
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

}
