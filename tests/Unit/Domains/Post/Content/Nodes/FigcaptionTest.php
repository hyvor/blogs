<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('json to HTML', function () {
    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figcaption',
            ],
        ],
    ];

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toBe('<figcaption></figcaption>');
});


test('html to json', function() {

    $html = '<figcaption>Hello</figcaption>';

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figcaption',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Hello',
                    ],
                ]
            ],
        ],
    ]));

});