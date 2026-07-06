<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Toc;

use Hyvor\Phrosemirror\Types\AttrsType;

class TocAttrs extends AttrsType
{
    /**
     * @var int[]
     */
    public array $levels = Toc::DEFAULT_LEVELS;
}
