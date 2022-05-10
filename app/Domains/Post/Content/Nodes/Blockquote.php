<?php

namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

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
