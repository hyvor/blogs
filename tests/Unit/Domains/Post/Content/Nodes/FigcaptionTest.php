<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figcaption',
            ],
        ],
    ];

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toBe('<figcaption></figcaption>');
});
