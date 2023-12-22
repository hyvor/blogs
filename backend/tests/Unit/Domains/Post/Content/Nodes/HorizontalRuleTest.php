<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('json to HTML', function () {
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'horizontal_rule',
            ],
        ],
    ]);

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toEqual('<hr>');
});

test('HTML to JSON', function () {
    $html = '<hr/>';

    $json = PostContentService::getJsonFromHtml($html, blog());

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
