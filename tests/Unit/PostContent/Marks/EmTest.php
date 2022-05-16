<?php
namespace App\Domains\Post\Content\Marks;

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

    $result = PostContentRepository::getHtml($document,  blog());

    expect($result)->toEqual('<em>Example Text</em>');
});