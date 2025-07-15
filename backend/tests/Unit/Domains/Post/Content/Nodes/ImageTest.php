<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Media\MediaRepository;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Route\PermalinkRepository;
use Illuminate\Http\UploadedFile;

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

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toBe("<img src=\"$src\" loading=\"lazy\" alt=\"$alt\" width=\"$width\" height=\"$height\">");
});

it('adds srcset for images in media', function () {
    $blog = blog();
    $file = UploadedFile::fake()->image('image.png', 2000);
    $media = MediaRepository::upload($blog, $file);

    $src = PermalinkRepository::getBaseUrl($blog) . '/media/' . $media->name;
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

    $html = PostContentService::getHtml($json, $blog);

    expect($html)->toBe(
        "<img src=\"$src\" loading=\"lazy\" alt=\"$alt\" width=\"$width\" height=\"$height\" srcset=\"$src 2000w, $src/500w 500w, $src/750w 750w, $src/1000w 1000w, $src/1500w 1500w\">"
    );
});

it('doesnt add larger widths to srcset', function () {
    $blog = blog();
    $file = UploadedFile::fake()->image('image.png', 850);
    $media = MediaRepository::upload($blog, $file);

    $src = PermalinkRepository::getBaseUrl($blog) . '/media/' . $media->name;
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

    $html = PostContentService::getHtml($json, $blog);

    expect($html)->toBe(
        "<img src=\"$src\" loading=\"lazy\" alt=\"$alt\" width=\"$width\" height=\"$height\" srcset=\"$src 850w, $src/500w 500w, $src/750w 750w\">"
    );
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

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toBe(
        "<figure><img src=\"$src\" loading=\"lazy\" alt=\"$alt\"><figcaption>$caption</figcaption></figure>"
    );
});


test('html to json', function () {
    $src = 'https://example.com/image.png';

    $html = "<img src=\"$src\">";

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figure',
                'content' => [
                    [
                        'type' => 'image',
                        'attrs' => [
                            'src' => $src,
                            'alt' => null,
                            'width' => null,
                            'height' => null,
                        ],
                    ],
                ]
            ]
        ],
    ]));
});

test('html to json with all attributes', function () {
    $src = 'https://example.com/image.png';
    $alt = 'ALT';
    $width = 100;
    $height = 200;

    $html = "<img src=\"$src\" alt=\"$alt\" width=\"$width\" height=\"$height\">";

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)->toBe(json_encode([
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
                            'width' => (string)$width,
                            'height' => (string)$height,
                        ],
                    ],
                ]
            ]
        ],
    ]));
});