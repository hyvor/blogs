<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes;

use Hyvor\Phrosemirror\Types\NodeType;

class Doc extends NodeType
{
    public string $name = 'doc';
}