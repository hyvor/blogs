<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Toc;

use Hyvor\Phrosemirror\Types\AttrsType;

class TocAttrs extends AttrsType
{

    /**
     * @var int[]
     */
    public array $levels = [1,2,3,4,5,6];

}