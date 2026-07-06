<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Types\NodeType;

class CustomHtml extends NodeType
{
    public string $name = 'custom_html';
    public ?string $content = 'text*';
    public string $group = 'block';

    public function toHtml($node, $children): string
    {
        $code = $node->allText();
        return "<p>$code</p>";
    }
}
