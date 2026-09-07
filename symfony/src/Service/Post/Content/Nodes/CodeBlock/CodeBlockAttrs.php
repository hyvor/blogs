<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\CodeBlock;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class CodeBlockAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    public ?string $language;
    public ?string $name;
    public ?string $annotations;
}
