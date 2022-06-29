<?php

namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;

/**
 * Custom HTML is added inside a <p> make sure margin
 */
class CustomHtml extends Node
{
    public static $name = 'custom_html';

    public static $marks = '';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'custom',
            ],
        ];
    }

    public function renderHTML($node)
    {
        $code = $node->content[0]->text ?? '';

        return [
            'content' => "<p>$code</p>",
        ];
    }
}
