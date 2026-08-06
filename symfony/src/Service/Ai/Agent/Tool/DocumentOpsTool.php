<?php

namespace App\Service\Ai\Agent\Tool;

use App\Service\Post\Content\Nodes\Paragraph;
use App\Service\Post\Content\Nodes\Text;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Document\TextNode;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'document_insert',
    description: 'insert a paragraph into the document',
    method: 'insert'
)]
class DocumentOpsTool
{

    public function __construct(
        private Node $document
    ) {}

    public function insert(string $contentTxt): string
    {
        $paragraph = new Node(new Paragraph);
        $paragraph->content->addNode(TextNode::fromTypeAndText(new Text(), $contentTxt));
        $this->document->content->addNode($paragraph);
        return 'Inserted paragraph successfully.';
    }

}
