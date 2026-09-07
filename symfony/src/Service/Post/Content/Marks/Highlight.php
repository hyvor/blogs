<?php declare(strict_types=1);

namespace App\Service\Post\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\MarkType;

class Highlight extends MarkType
{
    public string $name = 'highlight';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'mark'),
        ];
    }
}
