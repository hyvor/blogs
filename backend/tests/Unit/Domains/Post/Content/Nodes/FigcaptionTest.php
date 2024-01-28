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

    $html = '<figure><img src="https://image.com" /><figcaption>Hello</figcaption></figure>';

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figure',
                'content' => [
                    [
                        'type' => 'image',
                        'attrs' => [
                            'src' => 'https://image.com',
                            'alt' => null,
                            'width' => null,
                            'height' => null,
                        ],
                    ],
                    [
                        'type' => 'figcaption',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello',
                            ],
                        ]
                    ],
                ]
            ]
        ],
    ]));

});