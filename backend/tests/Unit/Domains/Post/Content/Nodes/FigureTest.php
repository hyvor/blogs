<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('json to HTML', function () {
    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figure',
            ],
        ],
    ];

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toBe('<figure></figure>');
});

test('html to json', function() {
    $html = '<figure>Hello</figure>';

    $json = PostContentService::getJsonFromHtml($html, blog(), false);

    expect($json)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figure',
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