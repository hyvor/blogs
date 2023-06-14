<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

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

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toEqual("<li>$content</li>");
});

test('HTML to JSON', function () {
    $content = 'A list item';

    $html = "<li>$content</li>";

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'list_item',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                        ],
                        /*[
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $content,
                                ],
                            ],
                        ],*/
                    ],
                ],
            ],
        ]));
});
