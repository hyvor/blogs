<?php declare(strict_types=1);

namespace App\Service\Post\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\MarkType;

class Strong extends MarkType
{
    public string $name = 'strong';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'strong'),
            new ParserRule(tag: 'b'),
        ];
    }
}
