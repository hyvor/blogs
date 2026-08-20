<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Toc;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class TocAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    /**
     * @var int[]
     */
    public array $levels = Toc::DEFAULT_LEVELS;
}
