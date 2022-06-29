<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    $content = 'I am a list item';

    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'list_item',
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

    expect($html)->toEqual("<li>$content</li>");
});

test('HTML to JSON', function () {
    $content = 'A list item';

    $html = "<li>$content</li>";

    $json = PostContentRepository::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'list_item',
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
                ],
            ],
        ]));
});
