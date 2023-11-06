<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Audio;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Audio extends NodeType
{
    public string $name = 'audio';
    public string $group = 'block';
    public string $attrs = AudioAttrs::class;

    public function toHtml(Node $node, $children): string
    {
        return '<audio controls src="' . $node->attr('src') . '"></audio>';
    }
}