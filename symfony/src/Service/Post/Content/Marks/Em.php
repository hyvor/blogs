<?php declare(strict_types=1);

namespace App\Service\Post\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\MarkType;

class Em extends MarkType
{
    public string $name = 'em';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'em'),
            new ParserRule(tag: 'i'),
        ];
    }
}
