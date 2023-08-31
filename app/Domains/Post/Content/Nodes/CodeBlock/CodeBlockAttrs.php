<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\CodeBlock;

use Hyvor\Phrosemirror\Types\AttrsType;

class CodeBlockAttrs extends AttrsType
{
    public ?string $language;
    public ?string $name;
    public ?string $annotations;
}