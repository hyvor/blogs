<?php

namespace Tests\Unit\Domains\Blog\Jobs;

use App\Domains\Blog\Jobs\UpdateUrlsJob;
use App\Domains\Cache\CacheService;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\User;
use Mockery;

it('updates URLs in content and content_unsaved', function() {

    $blog = blog();

    $mock = Mockery::mock(CacheService::class, [$blog])->makePartial();
    app()->bind(CacheService::class, fn() => $mock);
    $mock->shouldReceive('clearTemplateCache')->once();

    $oldUrl = 'https://1.com';
    $newUrl = 'https://2.com';

    $blog->setMeta([
        'logo_url' => $oldUrl . '/media/logo.png',
        'cover_url' => $oldUrl . '/media/cover.png',
        'icon_url' => $oldUrl . '/media/icon.png',
    ]);

    $post = Post::factory()
        ->has(
            PostVariant::factory()->state([
                'content' => json_encode([
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => $oldUrl . '/media/image.png'
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => 'Hello',
                            'marks' => [
                                [
                                    'type' => 'link',
                                    'attrs' => [
                                        'href' => $oldUrl . '/link'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'audio',
                            'attrs' => [
                                'src' => $oldUrl . '/media/audio.mp3'
                            ]
                        ]
                    ]
                ]),
                'content_unsaved' => json_encode([
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => $oldUrl . '/media/image.png'
                            ]
                        ]
                    ]
                ])
            ]),
            'variants'
        )
        ->create([
            'blog_id' => $blog,
            'featured_image_url' => $oldUrl . '/media/featured.png'
         ]);

    // blog safety
    $postAnotherBlog = Post::factory()
        ->has(
            PostVariant::factory()->state([
                'content' => json_encode([
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => $oldUrl . '/media/image.png'
                            ]
                        ]
                    ]
                ]),
            ]),
            'variants'
        )
        ->create([
            'blog_id' => blog()
        ]);

    $user = User::factory()->create([
        'blog_id' => $blog,
        'picture_url' => $oldUrl . '/media/user.png'
    ]);

    $job = new UpdateUrlsJob($blog, $oldUrl, $newUrl);
    $job->handle();

    /**
     * Blog
     */
    $blog->refresh();
    expect($blog->getMeta('cover_url'))->toBe($newUrl . '/media/cover.png');
    expect($blog->getMeta('logo_url'))->toBe($newUrl . '/media/logo.png');
    expect($blog->getMeta('icon_url'))->toBe($newUrl . '/media/icon.png');


    $post->refresh();
    /**
     * Post Meta
     */
    expect($post->featured_image_url)->toBe($newUrl . '/media/featured.png');

    /**
     * Post Content (more tested in ProsemirroHelperTest)
     */
    expect(json_decode($post->variants[0]->content, true)['content'][0]['attrs']['src'])
        ->toBe($newUrl . '/media/image.png');

    expect(json_decode($post->variants[0]->content, true)['content'][1]['marks'][0]['attrs']['href'])
        ->toBe($newUrl . '/link');

    expect(json_decode($post->variants[0]->content, true)['content'][2]['attrs']['src'])
        ->toBe($newUrl . '/media/audio.mp3');

    expect(json_decode($post->variants[0]->content_unsaved, true)['content'][0]['attrs']['src'])
        ->toBe($newUrl . '/media/image.png');

    expect(json_decode($postAnotherBlog->variants[0]->content, true)['content'][0]['attrs']['src'])
        ->toBe($oldUrl . '/media/image.png');

    /**
     * Authors
     */
    $user->refresh();
    expect($user->picture_url)->toBe($newUrl . '/media/user.png');

});