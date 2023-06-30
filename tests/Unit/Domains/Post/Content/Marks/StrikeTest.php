<?php

namespace App\Domains\Post\Content\_Marks;

use App\Domains\Post\Content\FromHtmlOptions;
use App\Domains\Post\Content\PostContentService;

test('strike JSON to HTML', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'strike',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentService::getHtml($document, blog());

    expect($result)->toEqual('<s>Example Text</s>');
});

test('from HTML', function() {

    $html = '<s>Example Text</s>';

    $result = PostContentService::getDocumentFromHtml($html, blog(), false);

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'strike',
                    ],
                ],
            ],
        ],
    ]);

});

test('from HTML strike', function() {

    $html = '<strike>Example Text</strike>';

    $result = PostContentService::getDocumentFromHtml($html, blog(), false);

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'strike',
                    ],
                ],
            ],
        ],
    ]);

});

test('from HTML del', function() {

    $html = '<del>Example Text</del>';

    $result = PostContentService::getDocumentFromHtml($html, blog(), false);

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'strike',
                    ],
                ],
            ],
        ],
    ]);

});