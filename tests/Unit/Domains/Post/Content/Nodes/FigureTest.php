<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figure',
            ],
        ],
    ];

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toBe('<figure></figure>');
});
