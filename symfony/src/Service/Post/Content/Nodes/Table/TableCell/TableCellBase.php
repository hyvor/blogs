<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Table\TableCell;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

abstract class TableCellBase extends NodeType
{
    public ?string $content = 'block+';
    public string $attrs = TableCellAttrs::class;

    abstract public function getTag(): string;

    public function toHtml(Node $node, string $children): string
    {
        $tag = $this->getTag();

        $attrs = [];
        /** @var int|string $rawColspan */
        $rawColspan = $node->attr('colspan');
        $colspan = intval($rawColspan);
        /** @var int|string $rawRowspan */
        $rawRowspan = $node->attr('rowspan');
        $rowspan = intval($rawRowspan);
        $colWidth = $node->attr('colwidth');

        if (is_array($colWidth)) {
            $width = array_sum($colWidth);
            $attrs['style'] = 'width: ' . $width . 'px;';
        }

        if ($colspan > 1) {
            $attrs['colspan'] = $colspan;
        }
        if ($rowspan > 1) {
            $attrs['rowspan'] = $rowspan;
        }

        $attrsString = '';
        if (count($attrs) > 0) {
            $attrsString = ' ' . implode(' ', array_map(function ($key, $value) {
                return "$key=\"$value\"";
            }, array_keys($attrs), array_values($attrs)));
        }

        return "<$tag$attrsString>$children</$tag>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: $this->getTag()),
        ];
    }
}
