<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes;

use App\Domains\Post\Content\PostContentRepository;
use App\Models\Blog;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Callout extends NodeType
{

    public string $name = 'callout';

    public function __construct() {}

    public function toHtml(Node $node, string $children): string
    {

        $bg = strval($node->attr('bg'));
        $fg = strval($node->attr('fg'));
        $emoji = strval($node->attr('emoji'));

        return "<aside style=\"background-color:$bg;color:$fg\"><span>$emoji</span><div>$children</div></aside>";

    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'aside')
        ];
    }

}