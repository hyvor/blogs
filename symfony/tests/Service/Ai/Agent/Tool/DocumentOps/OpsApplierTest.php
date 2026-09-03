<?php

namespace App\Tests\Service\Ai\Agent\Tool\DocumentOps;

use App\Service\Ai\Agent\Tool\DocumentOps\FetchedDocument;
use App\Service\Ai\Agent\Tool\DocumentOps\NodeIdMapBuilder;
use App\Service\Ai\Agent\Tool\DocumentOps\Op;
use App\Service\Ai\Agent\Tool\DocumentOps\OpDelete;
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

    private function fetchedDocument(Node $doc): FetchedDocument
    {
        $nodeIdMapBuilder = new NodeIdMapBuilder();
        $nodeIdMapBuilder->register($doc);

        return new FetchedDocument($doc, $nodeIdMapBuilder);
    }

    /**
     * Applies each op immediately, as DocumentOpsTool would, and returns the resulting document.
     */
    private function applyOps(FetchedDocument $fetchedDocument, Op ...$ops): Node
    {
        $opsApplier = new OpsApplier();
        foreach ($ops as $op) {
            $opsApplier->apply($fetchedDocument, $op);
        }

        return $fetchedDocument->getDocument();
    }

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
        $fetchedDoc = $this->fetchedDocument($doc);

        // top-level paragraph is registered as p-1
        $newDoc = $this->applyOps($fetchedDoc, new OpReplace('p-1', 'Goodbye, world!'));

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
        $fetchedDoc = $this->fetchedDocument($doc);

        // registration order: p-1 (top paragraph), quote-1 (blockquote), p-2 (paragraph inside blockquote)
        $newDoc = $this->applyOps(
            $fetchedDoc,
            new OpReplace('p-2', "This is a better blockquote.\n\nwith another paragraph.")
        );

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
        $fetchedDoc = $this->fetchedDocument($doc);

        $newDoc = $this->applyOps(
            $fetchedDoc,
            new OpInsert('p-1', "This is a new paragraph.\n\n>and another", false)
        );

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
        $fetchedDoc = $this->fetchedDocument($doc);

        $newDoc = $this->applyOps(
            $fetchedDoc,
            new OpInsert('p-2', 'A blockquote intro.', true)
        );

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
        $fetchedDoc = $this->fetchedDocument($doc);

        $newDoc = $this->applyOps(
            $fetchedDoc,
            new OpInsert('p-2', 'A blockquote outro.', false)
        );

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
        $fetchedDoc = $this->fetchedDocument($doc);

        // registration order: p-1 (Intro), ul-1, li-1, p-2 (Item one), li-2, p-3 (Item two)
        $newDoc = $this->applyOps(
            $fetchedDoc,
            new OpInsert('p-2', 'Item one, continued.', false),
            new OpReplace('p-3', "Item two A.\n\nItem two B.")
        );

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

    public function test_delete(): void
    {
        $doc = $this->nestedNode();
        $fetchedDoc = $this->fetchedDocument($doc);

        $newDoc = $this->applyOps($fetchedDoc, new OpDelete('p-2'));

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
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

    public function test_delete_unknown_node_id_is_noop(): void
    {
        $doc = $this->basicNode();
        $fetchedDoc = $this->fetchedDocument($doc);

        $newDoc = $this->applyOps($fetchedDoc, new OpDelete('p-99'));

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
                    ]
                ]
            ],
            $newDoc->toArray()
        );
    }

    public function test_replace_text(): void
    {
        $doc = $this->markedNode();
        $fetchedDoc = $this->fetchedDocument($doc);

        $newDoc = $this->applyOps($fetchedDoc, new OpReplaceText('p-1', 'foo', 'qux', 2));

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

    public function test_replace_assigns_a_fresh_id_to_the_new_node_never_reusing_the_replaced_id(): void
    {
        $doc = $this->basicNode();
        $fetchedDoc = $this->fetchedDocument($doc);

        $opsApplier = new OpsApplier();
        $opsApplier->apply($fetchedDoc, new OpReplace('p-1', 'First replacement.'));
        $opsApplier->apply($fetchedDoc, new OpInsert('p-2', 'Inserted afterwards.', false));

        $nodeIdMap = $fetchedDoc->getNodeIdMap();

        // p-1 (the original, now-detached node) must never be handed to a new node
        $this->assertArrayNotHasKey('p-1', $nodeIdMap);
        $this->assertArrayHasKey('p-2', $nodeIdMap);
        $this->assertArrayHasKey('p-3', $nodeIdMap);
        $this->assertSame(
            [
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'First replacement.']],
            ],
            $nodeIdMap['p-2']->toArray()
        );
        $this->assertSame(
            [
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'Inserted afterwards.']],
            ],
            $nodeIdMap['p-3']->toArray()
        );
    }

}
