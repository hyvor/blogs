<?php

namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;

class Figcaption extends Node
{
    public static $name = 'figcaption';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'figcaption',
            ],
        ];
    }

    public function renderHTML($node)
    {
        return ['figcaption', 0];
    }
}
