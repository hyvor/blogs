<?php

namespace App\Domains\Post\Content\Marks;

use Tiptap\Core\Mark;

class Strike extends Mark
{
    public static $name = 'strike';

    public function parseHTML()
    {
        return [
            [
                'tag' => 's',
            ],
            [
                'tag' => 'del',
            ],
            [
                'tag' => 'strike',
            ],
            [
                'style' => 'text-decoration=line-through',
            ],
        ];
    }

    public function renderHTML($mark)
    {
        return ['s', 0];
    }
}
