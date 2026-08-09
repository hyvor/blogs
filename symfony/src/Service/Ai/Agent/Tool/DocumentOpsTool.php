<?php

namespace App\Service\Ai\Agent\Tool;

use App\Service\Post\Content\Nodes\Paragraph;
use App\Service\Post\Content\Nodes\Text;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Document\TextNode;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'document_replace',
    description: 'replace a node in the document',
    method: 'replace'
)]
#[AsTool(
    name: 'document_insert',
    description: 'insert a paragraph into the document',
    method: 'insert'
)]
class DocumentOpsTool
{

    public function __construct(
        private ?Node $document = null,
    ) {}

    public function replace(
        string $nodeId,
        string $contentMarkdown,
    ): string
    {
        dump("Replaced content of node with ID $nodeId successfully.");
        return '';

        $node = $this->document->content->findNodeById($nodeId);
        if (!$node) {
            return "Node with ID $nodeId not found.";
        }
        $node->content->clear();
        $node->content->addNode(TextNode::fromTypeAndText(new Text(), $contentMarkdown));

        dump("Replaced content of node with ID $nodeId successfully.");

        return "Replaced content of node with ID $nodeId successfully.";
    }

    public function insert(string $contentMarkdown): string
    {
        dump('Inserted paragraph successfully.');
        return;

        $paragraph = new Node(new Paragraph);
        $paragraph->content->addNode(TextNode::fromTypeAndText(new Text(), $contentMarkdown));
        $this->document->content->addNode($paragraph);

        dump('Inserted paragraph successfully.');

        return 'Inserted paragraph successfully.';
    }

}
