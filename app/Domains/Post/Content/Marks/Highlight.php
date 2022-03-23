<?php

namespace App\Domains\Post\Content\Marks;

use Tiptap\Core\Mark as TiptapMark;

class Highlight extends TiptapMark
{
    public static $name = 'highlight';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'mark',
            ],
        ];
    }

    public function renderHTML($mark)
    {
        return ['mark', 0];
    }
}
