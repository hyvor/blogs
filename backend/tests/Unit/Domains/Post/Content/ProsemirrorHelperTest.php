<?php

namespace Tests\Unit\Domains\Post\Content;

use App\Domains\Post\Content\ProsemirrorHelper;

it('returns the array', function () {
    $array = [
        'type' => 'doc',
        'content' => [],
    ];

    $obj = (object) $array;

    expect(ProsemirrorHelper::getArrayJson($array)['type'])->toBe('doc');
    expect(ProsemirrorHelper::getArrayJson($obj)['type'])->toBe('doc');
    expect(ProsemirrorHelper::getArrayJson(json_encode($array))['type'])->toBe('doc');
    // empty doc
    expect(ProsemirrorHelper::getArrayJson(null)['type'])->toBe('doc');
});

it('finds blocks', function () {
    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'blockquote',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'test']],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'test2']],
                    ],
                ],
            ],
        ],
    ];

    $blocks = ProsemirrorHelper::findBlocks($json, 'paragraph');

    expect(count($blocks))->toBe(2);
    expect($blocks)->each(fn ($block) => expect($block->value['type'])->toBe('paragraph'));
});

it('updates blocks', function() {

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'blockquote',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'test']],
                    ],
                ],
            ],
        ],
    ];

    $data = ProsemirrorHelper::updateBlocks($json,
        function (array $node) {
            if ($node['type'] === 'blockquote') {
                $node['content'][] = [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'test2']],
                ];
            }
            return $node;
        }
    );

    expect(count($data['content'][0]['content']))->toBe(2);

});

it('updates URLs (images and links)', function() {

    $oldUrl = 'https://1.com';
    $src = $oldUrl . '/media/image.png';
    $newUrl = 'https://2.com';

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'image',
                'attrs' => [
                    'src' => $src
                ]
            ],
            [
                'type' => 'image',
                'attrs' => [
                    'src' => $oldUrl . '/media/image2.png'
                ]
            ],
            [
                'type' => 'image',
                'attrs' => [
                    'src' => 'https://anotherdomain.com/image.png'
                ]
            ],
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'marks' => [
                            [
                                'type' => 'link',
                                'attrs' => [
                                    'href' => $oldUrl . '/path'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
    ];

    $json = ProsemirrorHelper::updateUrls($json, $oldUrl, $newUrl);

    expect($json['content'][0]['attrs']['src'])->toBe($newUrl . '/media/image.png');
    expect($json['content'][1]['attrs']['src'])->toBe($newUrl . '/media/image2.png');
    expect($json['content'][2]['attrs']['src'])->toBe('https://anotherdomain.com/image.png');
    expect($json['content'][3]['content'][0]['marks'][0]['attrs']['href'])->toBe($newUrl . '/path');

});