<?php

namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class HorizontalRule extends Node
{
    public static $name = 'horizontal_rule';

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [],
        ];
    }

    public function parseHTML()
    {
        return [
            [
                'tag' => 'hr',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['hr', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes)];
    }
}
