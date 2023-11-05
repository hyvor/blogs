<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes;

use App\Helpers\TocHelper;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Converters\HtmlSerializer\Context;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Audio extends NodeType
{
    public string $name = 'audio';
    public string $group = 'block';

    public function toHtml(Node $node, $children): string
    {
        return '<audio controls src="' . $node->attr('src') . '"></audio>';
    }
}