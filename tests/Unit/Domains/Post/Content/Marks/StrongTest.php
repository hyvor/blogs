<?php

namespace App\Domains\Post\Content\Marks;

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

    $result = PostContentRepository::getHtml($document,  blog());

    expect($result)->toEqual('<strong>Example Text</strong>');
});
