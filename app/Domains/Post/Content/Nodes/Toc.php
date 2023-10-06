<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes;

use App\Helpers\TocHelper;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Converters\HtmlSerializer\Context;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Toc extends NodeType
{
    public string $name = 'toc';
    public string $group = 'block';

    public function toHtmlFromContext(Context $context): string
    {
       return TocHelper::tocToHtml($context);
    }
}