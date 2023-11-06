<?php declare(strict_types=1);

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentService;

test('Simple audio', function () {
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