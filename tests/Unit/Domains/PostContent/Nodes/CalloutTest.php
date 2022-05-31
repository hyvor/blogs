<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    $emoji = '🔥';
    $bg = '#000000';
    $fg = '#ffffff';
    $content = 'Some content';
    $bold = 'I am bold';

    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'callout',
                'attrs' => [
                    'emoji' => $emoji,
                    'bg' => $bg,
                    'fg' => $fg,
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $content,
                    ],
                    [
                        'type' => 'text',
                        'text' => $bold,
                        'marks' => [
                            [
                                'type' => 'bold',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)
        ->toEqual("<aside style=\"background-color:$bg;color:$fg\"><span>{$emoji}</span><div>$content$bold</div></aside>");
});


test('HTML to JSON', function () {
    $emoji = '🔥';
    $bg = '#000000';
    $fg = '#ffffff';
    $content = 'Some content';

    $html = "<aside style=\"background-color:$bg;color:$fg\" data-emoji=\"$emoji\">$content</aside>";

    $json = PostContentRepository::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'callout',
                    'attrs' => [
                        'emoji' => $emoji,
                        'bg' => $bg,
                        'fg' => $fg,
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
