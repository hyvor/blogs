<?php

namespace Tests\Unit\Domains\Post\Content;

use App\Domains\Post\Content\ProsemirrorHelper;

it('returns the array', function() {

    $array = [
        'type' => 'doc',
        'content' => []
    ];

    $obj = (object) $array;

    expect(ProsemirrorHelper::getArrayJson($array)['type'])->toBe('doc');
    expect(ProsemirrorHelper::getArrayJson($obj)['type'])->toBe('doc');
    expect(ProsemirrorHelper::getArrayJson(json_encode($array))['type'])->toBe('doc');
    // empty doc
    expect(ProsemirrorHelper::getArrayJson(null)['type'])->toBe('doc');

});

it('finds blocks', function() {

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'blockquote',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'test']]
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'test2']]
                    ]
                ]
            ]
        ]
    ];

    $blocks = ProsemirrorHelper::findBlocks($json, 'paragraph');

    expect(count($blocks))->toBe(2);
    expect($blocks)->each(fn ($block) => expect($block->value['type'])->toBe('paragraph'));

});