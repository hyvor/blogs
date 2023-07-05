<?php declare(strict_types=1);

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('json to HTML with ID', function () {
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

        $html = PostContentService::getHtml($json, blog());

        expect($html)->toEqual("<h$i id=\"$id\"><a href=\"#$id\">$content</a></h$i>");
    }
});

it('does not add anchor when there is a link inside', function() {

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
                            'marks' => [
                                [
                                    'type' => 'link',
                                    'attrs' => [
                                        'href' => 'https://example.com',
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ]);

        $html = PostContentService::getHtml($json, blog());

        expect($html)
            ->toEqual(
                "<h$i id=\"$id\"><a href=\"https://example.com\" target=\"_blank\" rel=\"noopener noreferrer\">$content</a></h$i>"
            );
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

        $html = PostContentService::getHtml($json, blog());

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

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toEqual('<h2></h2>');
});

test('HTML to JSON', function () {
    $content = 'A h2';
    $id = 'custom-id';

    $html = "<h2 id=\"$id\">$content</h2>";

    $json = PostContentService::getJsonFromHtml($html, blog());

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
