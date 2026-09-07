<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Toc;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Toc extends NodeType
{
    public const DEFAULT_LEVELS = [1, 2, 3, 4, 5, 6];

    public string $name = 'toc';
    public ?string $content = null;
    public string $group = 'block';
    public string $attrs = TocAttrs::class;

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
                    $levels = @explode(',', $levels);
                    $levels = array_map('intval', $levels);

                    return TocAttrs::fromArray([
                        'levels' => $levels,
                    ]);
                }
            ),
        ];
    }
}
