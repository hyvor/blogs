<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Table;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Table extends NodeType
{

    public string $name = 'table';
    public ?string $content = 'table_row+';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        return "<div class=\"table-container\"><table>$children</table></div>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'table')
        ];
    }

}