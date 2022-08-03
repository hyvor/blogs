<?php

namespace App\Domains\Post\Content\Marks;

use App\Domains\Post\Content\PostContentRepository;

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

    $result = PostContentRepository::getHtml($document, blog());

    expect($result)->toEqual('<s>Example Text</s>');
});
