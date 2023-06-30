<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\Nodes\Callout\Callout;
use App\Domains\Post\Content\PostContentService;

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
                                'type' => 'strong',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $html = PostContentService::getHtml($json, blog());

    expect($html)
        ->toEqual("<aside style=\"background-color:$bg;color:$fg\"><span>{$emoji}</span><div>$content<strong>$bold</strong></div></aside>");
});

test('HTML to JSON', function () {
    $emoji = '🔥';
    $bg = '#000000';
    $fg = '#ffffff';
    $content = 'Some content';

    $html = "<aside style=\"background-color:$bg;color:$fg\"><span>$emoji</span><div>$content</div></aside>";

    $json = PostContentService::getJsonFromHtml($html, blog());

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


it('works when no div is inside', function() {

    $html = "<aside>Some <b>content</b></aside>";

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'callout',
                    'attrs' => [
                        'emoji' => Callout::DEFAULT_EMOJI,
                        'bg' => Callout::DEFAULT_BG,
                        'fg' => Callout::DEFAULT_FG,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some ',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'content',
                            'marks' => [
                                [
                                    'type' => 'strong',
                                ],
                            ],
                        ]
                    ],
                ],
            ],
        ]));

});