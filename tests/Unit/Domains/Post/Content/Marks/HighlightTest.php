<?php

namespace App\Domains\Post\Content\_Marks;

use App\Domains\Post\Content\PostContentRepository;

test('code JSON to HTML', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'highlight',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentRepository::getHtml($document, blog());

    expect($result)->toEqual('<mark>Example Text</mark>');
});



test('from HTML', function() {

    $html = '<mark>Example Text</mark>';

    $result = PostContentRepository::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'highlight',
                    ],
                ],
            ],
        ],
    ]);

});
