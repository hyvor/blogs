<?php

namespace App\Domains\Post\Content\Marks;

use Tiptap\Core\Mark;

class Sub extends Mark
{
    public static $name = 'sub';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'sub',
            ],
        ];
    }

    public function renderHTML($mark)
    {
        return ['sub', 0];
    }
}
