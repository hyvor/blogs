<?php

namespace App\Domains\Post\Content\_Marks;

use App\Domains\Post\Content\PostContentRepository;

test('em JSON to HTML', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'em',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentRepository::getHtml($document, blog());

    expect($result)->toEqual('<em>Example Text</em>');
});

test('em HTML to JSON', function() {

    $html = '<em>Example Text</em>';

    $result = PostContentRepository::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'em',
                    ],
                ],
            ],
        ],
    ]);

});

test('from HTML from i tag', function() {

    $html = '<i>Example Text</i>';

    $result = PostContentRepository::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'em',
                    ],
                ],
            ],
        ],
    ]);

});
