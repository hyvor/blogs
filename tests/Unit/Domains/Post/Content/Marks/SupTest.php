<?php

namespace App\Domains\Post\Content\_Marks;

use App\Domains\Post\Content\PostContentRepository;

test('sup JSON to HTML', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'sup',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentRepository::getHtml($document, blog());

    expect($result)->toEqual('<sup>Example Text</sup>');
});


test('from HTML', function() {

    $html = '<sup>Example Text</sup>';

    $result = PostContentRepository::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'sup',
                    ],
                ],
            ],
        ],
    ]);

});