<?php

namespace Tests\Unit\Domains\Post\Content;

use App\Domains\Post\Content\HtmlParser;
use Database\Factories\BlogFactory;
use Tests\Case\DatabaseTestCase;

class HtmlParserTest extends DatabaseTestCase
{

    public function test_converts_p_a_img_to_img(): void
    {

        $html = <<<HTML
            <p>
                <a href="https://example.com">
                    <img src="https://example.com/image.jpg" alt="Example Image">
                </a>
            </p>
        HTML;

        $parser = new HtmlParser($html);
        $doc = $parser->parse(BlogFactory::one());

        $this->assertEquals(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'figure',
                        'content' => [
                            [
                                'type' => 'image',
                                'attrs' => [
                                    'src' => 'https://example.com/image.jpg',
                                    'alt' => 'Example Image',
                                    'width' => null,
                                    'height' => null,
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            $doc->toArray()
        );

    }

    public function test_converts_p_a_img_to_img_keeps_text(): void
    {

        $html = <<<HTML
            <p>
                <a href="https://example.com">
                    <img src="https://example.com/image.jpg" alt="Example Image">
                </a> <strong>More text</strong></p>
        HTML;

        $parser = new HtmlParser($html);
        $doc = $parser->parse(BlogFactory::one());

        $this->assertEquals(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'figure',
                        'content' => [
                            [
                                'type' => 'image',
                                'attrs' => [
                                    'src' => 'https://example.com/image.jpg',
                                    'alt' => 'Example Image',
                                    'width' => null,
                                    'height' => null,
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => ' ',
                            ],
                            [
                                'type' => 'text',
                                'text' => 'More text',
                                'marks' => [
                                    [
                                        'type' => 'strong',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            $doc->toArray()
        );

    }

}
