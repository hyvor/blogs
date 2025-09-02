<?php

namespace Tests\Unit\Domains\Post\Content\Nodes;

use App\Domains\Media\MediaRepository;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Route\PermalinkRepository;
use Database\Factories\BlogFactory;
use Illuminate\Http\UploadedFile;
use Tests\Case\DatabaseTestCase;

class ImageTest extends DatabaseTestCase
{

    public function test_json_to_html(): void
    {
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

        $html = PostContentService::getHtml($json, BlogFactory::one());

        $this->assertSame("<img src=\"$src\" loading=\"lazy\" alt=\"$alt\" width=\"$width\" height=\"$height\">", $html);
    }

    public function test_adds_srcset_for_images_in_media(): void
    {
        $blog = BlogFactory::one();
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

        $this->assertSame(
            "<img src=\"$src\" loading=\"lazy\" alt=\"$alt\" width=\"$width\" height=\"$height\" srcset=\"$src 2000w, $src/500w 500w, $src/750w 750w, $src/1000w 1000w, $src/1500w 1500w\">",
            $html
        );
    }

    public function test_doesnt_add_larger_widths_to_srcset(): void
    {
        $blog = BlogFactory::one();
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

        $this->assertSame(
            "<img src=\"$src\" loading=\"lazy\" alt=\"$alt\" width=\"$width\" height=\"$height\" srcset=\"$src 850w, $src/500w 500w, $src/750w 750w\">",
            $html
        );
    }

    public function test_json_to_html_with_figure(): void
    {
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

        $html = PostContentService::getHtml($json, BlogFactory::one());

        $this->assertSame(
            "<figure><img src=\"$src\" loading=\"lazy\" alt=\"$alt\"><figcaption>$caption</figcaption></figure>",
            $html
        );
    }


    public function test_html_to_json(): void
    {
        $src = 'https://example.com/image.png';

        $html = "<img src=\"$src\">";

        $json = PostContentService::getJsonFromHtml($html, BlogFactory::one());

        $this->assertSame(json_encode([
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
        ]), $json);
    }

    public function test_html_to_json_with_all_attributes(): void
    {
        $src = 'https://example.com/image.png';
        $alt = 'ALT';
        $width = 100;
        $height = 200;

        $html = "<img src=\"$src\" alt=\"$alt\" width=\"$width\" height=\"$height\">";

        $json = PostContentService::getJsonFromHtml($html, BlogFactory::one());

        $this->assertSame(json_encode([
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
        ]), $json);
    }

}