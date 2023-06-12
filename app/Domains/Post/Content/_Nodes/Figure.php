<?php

namespace App\Domains\Post\Content\_Nodes;

use Tiptap\Core\Node;

class Figure extends Node
{
    public static $name = 'figure';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'figure',
            ],
        ];
    }

    public function renderHTML($node)
    {
        return ['figure', 0];
    }
}
