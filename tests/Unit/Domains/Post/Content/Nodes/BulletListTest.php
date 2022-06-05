<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    $content = 'I am a list item';

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'bullet_list',
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
            ],
        ],
    ];

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toEqual("<ul><li><p>$content</p></li><li><p>$content</p></li></ul>");
});

test('HTML to JSON', function () {
    $content = 'A list item';

    $html = "<ul><li><p>$content</p></li></ul>";

    $json = PostContentRepository::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bullet_list',
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
                ],
            ],
        ]));
});
