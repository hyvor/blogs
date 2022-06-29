<?php

namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;

class Image extends Node
{
    public static $name = 'image';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'img[src]',
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'src' => null,
            'alt' => null,
            'width' => null,
            'height' => null,
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['img', $HTMLAttributes, 0];
    }
}
