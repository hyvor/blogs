<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    $src = 'https://example.com/image.png';
    $alt = 'ALT';
    $width = 100;
    $height = 200;

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'image',
                'attrs' => [
                    'src' => $src,
                    'alt' => $alt,
                    'width' => $width,
                    'height' => $height,
                ],
            ],
        ],
    ];

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toBe("<img src=\"$src\" alt=\"$alt\" width=\"$width\" height=\"$height\">");
});

test('json to HTML with figure', function () {
    $src = 'https://example.com/image.png';
    $alt = 'ALT';
    $caption = 'Caption';

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figure',
                'content' => [
                    [
                        'type' => 'image',
                        'attrs' => [
                            'src' => $src,
                            'alt' => $alt,
                        ],
                    ],
                    [
                        'type' => 'figcaption',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $caption,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toBe("<figure><img src=\"$src\" alt=\"$alt\"><figcaption>$caption</figcaption></figure>");
});
