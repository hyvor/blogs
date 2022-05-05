<?php

namespace App\Domains\Post\Content\Nodes;

use Tiptap\Core\Node;

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

    public function addAttributes()
    {
        return [
            'emoji' => [
                'parseHTML' => function ($DOMNode) {
                    $children = $DOMNode->childNodes;

                    foreach ($children as $child) {
                        if ($child->nodeName === 'span') {
                            return $child->nodeValue;
                        }
                    }

                    return null;
                },
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
