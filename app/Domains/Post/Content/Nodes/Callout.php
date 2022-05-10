<?php

namespace App\Domains\Post\Content\Nodes;

use App\Domains\Post\Content\PostContentRepository;
use Tiptap\Core\Node;
use Tiptap\Utils\InlineStyle;

class Callout extends Node
{
    public static $name = 'callout';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'aside',
                'getAttrs' => function ($DOMNode) {

                }
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'emoji' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-emoji'),
                'rendered' => false
            ],
            'bg' => [
                'parseHTML' => fn ($DOMNode) => InlineStyle::getAttribute($DOMNode, 'background-color'),
                'rendered' => false,
            ],
            'fg' => [
                'parseHTML' => fn ($DOMNode) => InlineStyle::getAttribute($DOMNode, 'color'),
                'rendered' => false
            ],
        ];
    }

    public function renderHTML($node)
    {
        $content = $node->content ?? [];

        /**
         * Render inside content
         *
         * Based on my tests, there's currently no way to set
         * innerHTML of a non-content element (<span> in this case)
         * using the array syntax.
         *
         * So, I had to use "content" method. When using that, we have to separately calculate the inside contents.
         */
        $inside = PostContentRepository::getHtml([
            'type' => 'doc',
            'content' => $content
        ], $this->options['blog']);

        return [
            'content' => "<aside style=\"background-color:{$node->attrs->bg};color:{$node->attrs->fg}\"><span>{$node->attrs->emoji}</span><div>$inside</div></aside>"
        ];

    }
}
