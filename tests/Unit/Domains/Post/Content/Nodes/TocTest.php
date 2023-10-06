<?php declare(strict_types=1);

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('simple TOC to HTML', function () {
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'toc',
                'attrs' => [
                    'levels' => [1,2,3,4],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 1,
                    'id' => 'my-big-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My big heading',
                    ],
                ],
            ]
        ]
    ]);
    
    $html = PostContentService::getHtml($json, blog());
    expect($html)->toEqual('<ul><li><a href="#my-big-heading">My big heading</a></li></ul><h1 id="my-big-heading"><a href="#my-big-heading">My big heading</a></h1>');
});

test('complex TOC to HTML', function () {
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'toc',
                'attrs' => [
                    'levels' => [1,2,3,4],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 1,
                    'id' => 'my-big-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My big heading',
                    ],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 2,
                    'id' => 'my-smaller-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My smaller heading',
                    ],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 3,
                    'id' => 'my-little-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My little heading',
                    ],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 3,
                    'id' => 'my-little-heading2',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My little heading 2',
                    ],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 1,
                    'id' => 'my-big-heading-2',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My big heading 2',
                    ],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 5,
                    'id' => 'my-way-smaller-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My way smaller heading',
                    ],
                ],
            ],
        ]
    ]);
    
    $html = PostContentService::getHtml($json, blog());
    expect($html)->toEqual('<ul><li><a href="#my-big-heading">My big heading</a></li><ul><li><a href="#my-smaller-heading">My smaller heading</a></li><ul><li><a href="#my-little-heading">My little heading</a></li><li><a href="#my-little-heading2">My little heading 2</a></li></ul></ul><li><a href="#my-big-heading-2">My big heading 2</a></li><ul><li><a href="#my-way-smaller-heading">My way smaller heading</a></li></ul></ul><h1 id="my-big-heading"><a href="#my-big-heading">My big heading</a></h1><h2 id="my-smaller-heading"><a href="#my-smaller-heading">My smaller heading</a></h2><h3 id="my-little-heading"><a href="#my-little-heading">My little heading</a></h3><h3 id="my-little-heading2"><a href="#my-little-heading2">My little heading 2</a></h3><h1 id="my-big-heading-2"><a href="#my-big-heading-2">My big heading 2</a></h1><h5 id="my-way-smaller-heading"><a href="#my-way-smaller-heading">My way smaller heading</a></h5>');
});