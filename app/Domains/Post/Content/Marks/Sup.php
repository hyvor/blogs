<?php

namespace App\Domains\Post\Content\Marks;

use Tiptap\Core\Mark;

class Sup extends Mark
{
    public static $name = 'sup';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'sup',
            ],
        ];
    }

    public function renderHTML($mark)
    {
        return ['sup', 0];
    }
}
