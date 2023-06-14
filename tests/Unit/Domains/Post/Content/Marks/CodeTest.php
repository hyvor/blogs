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
                        'type' => 'code',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentService::getHtml($document, blog());

    expect($result)->toEqual('<code>Example Text</code>');
});

test('from HTML', function() {

    $html = '<code>Example Text</code>';

    $result = PostContentService::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'code',
                    ],
                ],
            ],
        ],
    ]);

});
