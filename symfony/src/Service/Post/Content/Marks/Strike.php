<?php declare(strict_types=1);

namespace App\Service\Post\Content\Marks;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\MarkType;

class Strike extends MarkType
{
    public string $name = 'strike';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 's'),
            new ParserRule(tag: 'del'),
            new ParserRule(tag: 'strike'),
        ];
    }
}
