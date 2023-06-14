<?php

namespace App\Domains\Post\Content\_Marks;

use App\Domains\Post\Content\PostContentService;

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

    $result = PostContentService::getHtml($document, blog());

    expect($result)->toEqual('<mark>Example Text</mark>');
});



test('from HTML', function() {

    $html = '<mark>Example Text</mark>';

    $result = PostContentService::getDocumentFromHtml($html, blog());

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
