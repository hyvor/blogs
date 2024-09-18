<?php declare(strict_types=1);

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('JSON to HTML', function () {
    $src = 'https://file-examples.com/storage/fed001dfc36547d0292f8e5/2017/11/file_example_MP3_1MG.mp3';
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'audio',
                'attrs' => [
                    'src' => $src,
                ],
            ],
        ]
    ]);

    $html = PostContentService::getHtml($json, blog());
    expect($html)->toEqual('<audio controls src="https://file-examples.com/storage/fed001dfc36547d0292f8e5/2017/11/file_example_MP3_1MG.mp3"></audio>');
});

test('HTML to JSON', function() {

    $content = 'A blockquote';
    $src = 'https://example.com/audio.mp3';
    $html = "<audio controls src='$src'></audio>";
    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'audio',
                    'attrs' => [
                        'src' => $src,
                    ],
                ],
            ],
        ]));


});