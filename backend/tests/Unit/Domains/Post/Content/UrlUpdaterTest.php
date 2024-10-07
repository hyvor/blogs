<?php

namespace Tests\Unit\Domains\Post\Content;


use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\Content\UrlUpdater;

it('url updater test', function() {

    it('updates from old url to new url', function() {

        $oldUrl = 'https://old.com';
        $newUrl = 'https://new.com';

        $blog = blog();
        $doc = PostContentService::getDocumentFromJson([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => 'https://old.com/media/image.jpg'
                    ]
                ],
                [
                    'type' => 'audio',
                    'attrs' => [
                        'src' => 'https://old.com/media/audio.mp3'
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'test',
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => [
                                'href' => 'https://old.com/page'
                            ]
                        ]
                    ]
                ]
            ]
        ], $blog);

        $updater = new UrlUpdater($doc);
        $updated = $updater->updateFromOldToNew($oldUrl, $newUrl);

        expect(json_encode($updated->toJson(), true))->toBe([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => 'https://new.com/media/image.jpg'
                    ]
                ],
                [
                    'type' => 'audio',
                    'attrs' => [
                        'src' => 'https://new.com/media/audio.mp3'
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'test',
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => [
                                'href' => 'https://new.com/page'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

    });

});
