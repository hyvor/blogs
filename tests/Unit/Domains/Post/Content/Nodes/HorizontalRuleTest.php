<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;

test('json to HTML', function () {
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'horizontal_rule',
            ],
        ],
    ]);

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toEqual('<hr>');
});

test('HTML to JSON', function () {
    $html = '<hr/>';

    $json = PostContentRepository::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'horizontal_rule',
                ],
            ],
        ]));
});
