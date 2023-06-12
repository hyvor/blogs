<?php

namespace App\Domains\Post\Content\_Nodes;

use Tiptap\Core\Node;

class Blockquote extends Node
{
    public static $name = 'blockquote';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'blockquote',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['blockquote', 0];
    }
}
