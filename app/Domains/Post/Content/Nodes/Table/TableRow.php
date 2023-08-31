<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Table;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class TableRow extends NodeType
{

    public string $name = 'table_row';
    public ?string $content = '(table_cell | table_header)*';

    public function toHtml(Node $node, string $children): string
    {
        return "<tr>$children</tr>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'tr')
        ];
    }

}