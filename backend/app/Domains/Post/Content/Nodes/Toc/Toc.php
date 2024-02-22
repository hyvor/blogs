<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Toc;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;
use Hyvor\Phrosemirror\Converters\HtmlSerializer\Context;

class Toc extends NodeType
{

    public string $name = 'toc';
    public ?string $content = null;
    public string $group = 'block';
    public string $attrs = TocAttrs::class;

    public function toHtmlFromContext(Context $context): string
    {
        $tocHtml = new TocHtml(
            $context->topNode,
            $context->node->attrs->levels
        );
        return $tocHtml->toHtml();
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'div',
                getAttrs: function ($node) {
                    if ($node->getAttribute('class') !== 'toc') {
                        return false;
                    }

                    $levels = $node->getAttribute('data-levels');

                    $levels = @explode(',', $levels) ?: [1,2,3,4,5,6];
                    $levels = array_map('intval', $levels);

                    return TocAttrs::fromArray([
                        'levels' => $levels
                    ]);
                }
            )
        ];
    }

}