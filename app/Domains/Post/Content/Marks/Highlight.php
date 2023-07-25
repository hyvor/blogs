<?php

namespace App\Domains\Post\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Highlight extends MarkType
{

    public string $name = 'highlight';

    public function toHtml(Mark $mark, string $children): string
    {
        return "<mark>$children</mark>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'mark')
        ];
    }

}
