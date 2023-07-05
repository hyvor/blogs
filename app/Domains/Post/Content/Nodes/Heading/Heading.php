<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Heading;

use App\Models\Blog;
use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Heading extends NodeType
{

    private const ALLOWED_LEVELS = [1, 2, 3, 4, 5, 6];

    public string $name = 'heading';
    public string $attrs = HeadingAttrs::class;
    public ?string $content = 'inline*';
    public string $group = 'block';

    public function __construct(private Blog $blog) {}

    public function toHtml(Node $node, string $children): string
    {

        $level = intval($node->attr('level'));
        $level = in_array($level, self::ALLOWED_LEVELS) ? $level : 2;

        /** @var ?string $id */
        $id = $node->attr('id');
        $idAttr = $id ? " id=\"$id\"" : null;

        // add anchor link if there is no link inside
        if (
            $id &&
            !preg_match('/<a\b[^>]*>.*<\/a>/', $children) &&
            $this->blog->getMeta('heading_anchors')
        ) {
            $children = "<a href=\"#$id\">$children</a>";
        }

        return "<h$level$idAttr>$children</h$level>";

    }

    public function fromHtml(): array
    {
        return array_map(function (int $level) {
            return new ParserRule(
                tag: "h{$level}",
                getAttrs: function (DOMElement $node) use ($level) {
                    return HeadingAttrs::fromArray([
                        'level' => $level,
                        'id' => $node->getAttribute('id'),
                    ]);
                },
            );
        }, self::ALLOWED_LEVELS);
    }

}