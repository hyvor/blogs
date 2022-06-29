<?php

namespace App\Domains\Post\Content\Marks;

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
                        'type' => 'code',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentRepository::getHtml($document,  blog());

    expect($result)->toEqual('<code>Example Text</code>');
});
