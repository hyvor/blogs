<?php

namespace App\Domains\Post\Content\_Nodes;

use Tiptap\Core\Node;

class HardBreak extends Node
{
    public static $name = 'hard_break';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'br',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['br'];
    }
}
