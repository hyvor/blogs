<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('JSON to HTML', function () {
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'hard_break',
            ],
        ],
    ]);

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toEqual("<br>");
});
