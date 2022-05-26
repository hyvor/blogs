<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    foreach (range(1, 6) as $i) {
        $content = "I am a h$i";
        $id = 'custom-id';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => $i,
                        'id' => $id,
                    ],
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

        expect($html)->toEqual("<h$i id=\"$id\">$content</h$i>");
    }
});

test('json to HTML without ID', function () {
    foreach (range(1, 6) as $i) {
        $content = "I am a h$i";

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => $i,
                    ],
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

        expect($html)->toEqual("<h$i>$content</h$i>");
    }
});

test('h7 is h2', function () {
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 7,
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => '',
                    ],
                ],
            ],
        ],
    ]);

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toEqual("<h2></h2>");
});

test('HTML to JSON', function () {
    $content = 'A h2';
    $id = 'custom-id';

    $html = "<h2 id=\"$id\">$content</h2>";

    $json = PostContentRepository::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                        'id' => $id,
                    ],
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
