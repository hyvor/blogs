<?php
namespace App\Domains\Post\Content\Marks;

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

    $result = PostContentRepository::getHtml($document,  blog());

    expect($result)->toEqual('<sup>Example Text</sup>');
});