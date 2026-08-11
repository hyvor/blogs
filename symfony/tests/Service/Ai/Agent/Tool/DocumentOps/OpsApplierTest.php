<?php

namespace App\Tests\Service\Ai\Agent\Tool\DocumentOps;

use App\Service\Ai\Agent\Tool\DocumentOps\FetchedDocument;
use App\Service\Ai\Agent\Tool\DocumentOps\OpReplace;
use App\Service\Ai\Agent\Tool\DocumentOps\OpsApplier;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Phrosemirror\Document\Node;

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

}
