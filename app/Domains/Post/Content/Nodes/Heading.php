<?php

namespace App\Domains\Post\Content\Nodes;

use DOMElement;
use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class Heading extends Node
{
    public static $name = 'heading';

    public function addOptions()
    {
        return [
            'levels' => [1, 2, 3, 4, 5, 6],
        ];
    }

    public function addAttributes()
    {
        return [
            'id' => [],
        ];
    }

    public function parseHTML()
    {
        return array_map(function ($level) {
            return [
                'tag' => "h{$level}",
                'getAttrs' => function (DOMElement $node) use ($level) {
                    return [
                        'level' => $level,
                        'id' => $node->getAttribute('id')
                    ];
                }
            ];
        }, $this->options['levels']);
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        $hasLevel = in_array($node->attrs->level, $this->options['levels']);

        $level = $hasLevel ?
            $node->attrs->level :
            2;

        return [
            "h{$level}",
            $HTMLAttributes,
            0,
        ];
    }
}
