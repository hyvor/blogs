<?php

namespace App\Domains\Post\Content\_Marks;

use App\Domains\Post\Content\PostContentRepository;

test('sub JSON to HTML', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'sub',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentRepository::getHtml($document, blog());

    expect($result)->toEqual('<sub>Example Text</sub>');
});


test('from HTML', function() {

    $html = '<sub>Example Text</sub>';

    $result = PostContentRepository::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'sub',
                    ],
                ],
            ],
        ],
    ]);

});