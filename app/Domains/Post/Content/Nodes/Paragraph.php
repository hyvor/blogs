<?php
namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class Paragraph extends Node
{
    public static $name = 'paragraph';

    public static $priority = 1000;

    public function parseHTML()
    {
        return [
            [
                'tag' => 'p',
            ],
        ];
    }

    public function renderHTML($node)
    {
        return ['p', 0];
    }
}
