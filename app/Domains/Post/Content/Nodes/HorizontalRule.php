<?php

namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;

class HorizontalRule extends Node
{
    public static $name = 'horizontal_rule';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'hr',
            ],
        ];
    }

    public function renderHTML($node)
    {
        return ['hr'];
    }
}
