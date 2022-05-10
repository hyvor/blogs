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
                        'type' => 'highlight',
                    ],
                ],
            ],
        ],
    ];

    $result = PostContentRepository::getHtml($document,  blog());

    expect($result)->toEqual('<mark>Example Text</mark>');
});