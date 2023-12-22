<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('JSON to HTML', function () {
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'hard_break',
            ],
        ],
    ]);

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toEqual('<br>');
});
