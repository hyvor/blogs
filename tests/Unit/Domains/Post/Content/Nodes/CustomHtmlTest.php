<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('json to HTML', function () {
    $code = '<div><b>Bold text <i>Name</i></b></div>';

    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'custom_html',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $code,
                    ],
                ],
            ],
        ],
    ]);

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toBe("<p>$code</p>");
});
