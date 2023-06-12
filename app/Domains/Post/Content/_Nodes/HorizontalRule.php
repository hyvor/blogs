<?php

namespace App\Domains\Post\Content\_Nodes;

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
