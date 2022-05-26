<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    $content = 'I am a blockquote';

    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'blockquote',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $content,
                    ],
                ],
            ],
        ],
    ]);

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toEqual("<blockquote>$content</blockquote>");
});



test('HTML to JSON', function () {
    $content = 'A blockquote';

    $html = "<blockquote>$content</blockquote>";

    $json = PostContentRepository::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
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
