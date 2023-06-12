<?php

namespace App\Domains\Post\Content\_Marks;

use App\Domains\Post\Content\PostContentRepository;

test('strong JSON to HTML', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'strong',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentRepository::getHtml($document, blog());

    expect($result)->toEqual('<strong>Example Text</strong>');
});

test('from HTML b', function() {

    $html = '<b>Example Text</b>';

    $result = PostContentRepository::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'strong',
                    ],
                ],
            ],
        ],
    ]);

});

test('from HTML strong', function() {

    $html = '<strong>Example Text</strong>';

    $result = PostContentRepository::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'strong',
                    ],
                ],
            ],
        ],
    ]);

});
