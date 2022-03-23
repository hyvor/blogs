<?php
namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class Callout extends Node
{
    public static $name = 'callout';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'aside',
            ],
        ];
    }

    public function renderHTML($node)
    {
        $content = $node->content[0]->text ?? '';

        return [
            'content' => <<<HTML
                <aside>
                    <span>{$node->attrs->emoji}</span>
                    <div>{$content}</div>
                </aside>
            HTML
        ];
    }
}
