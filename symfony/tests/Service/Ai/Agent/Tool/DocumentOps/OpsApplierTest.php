<?php

namespace App\Tests\Service\Ai\Agent\Tool\DocumentOps;

use App\Service\Ai\Agent\Tool\DocumentOps\FetchedDocument;
use App\Service\Ai\Agent\Tool\DocumentOps\OpInsert;
use App\Service\Ai\Agent\Tool\DocumentOps\OpReplace;
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

}
