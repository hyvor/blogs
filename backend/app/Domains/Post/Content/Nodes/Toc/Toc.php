<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Toc;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Toc extends NodeType
{

    public string $name = 'toc';
    public ?string $content = null;
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        return '<x-toc></x-toc>';
    }

    public function fromHtml(): array
    {
        return [];
    }

}