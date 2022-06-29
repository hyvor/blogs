<?php

namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class OrderedList extends Node
{
    public static $name = 'ordered_list';

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
                'tag' => 'ol',
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'order' => [
                'parseHTML' => fn ($DOMNode) => (int) $DOMNode->getAttribute('start') ?: null,
                'renderHTML' => fn ($attributes) => ($attributes->order ?? null) ? ['start' => $attributes->order] : null,
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['ol', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes), 0];
    }
}
