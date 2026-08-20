<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class HardBreak extends NodeType
{
    public string $name = 'hard_break';
    public string $attrs = HardBreakAttrs::class;
    public string $group = 'inline';
    public bool $inline = true;

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'br'),
        ];
    }
}
