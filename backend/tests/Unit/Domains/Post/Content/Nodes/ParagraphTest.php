<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('json to HTML', function () {
    $content = 'I am a paragraph';

    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $content,
                    ],
                ],
            ],
        ],
    ]);

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toEqual("<p>$content</p>");
});

test('HTML to JSON', function () {
    $content = 'A paragraph';

    $html = "<p>$content</p>";

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                        ],
                    ],
                ],
            ],
        ]));
});
