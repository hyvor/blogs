<?php

namespace App\Domains\Post\Content\Marks;

use Tiptap\Core\Mark;

class Code extends Mark
{
    public static $name = 'code';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'code',
            ],
        ];
    }

    public function renderHTML($mark)
    {
        return ['code', 0];
    }
}
