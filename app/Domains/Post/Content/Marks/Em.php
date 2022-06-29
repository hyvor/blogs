<?php

namespace App\Domains\Post\Content\Marks;

use Tiptap\Core\Mark;

class Em extends Mark
{
    public static $name = 'em';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'em',
            ],
        ];
    }

    public function renderHTML($mark)
    {
        return ['em', 0];
    }
}
