<?php

namespace Tests\Unit\Domains\Blog\Jobs;


use App\Domains\Blog\Jobs\UpdateMediaUrlsInPostsJob;

it('updates media urls', function() {

    $contentDoc = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'image',
                'attrs' => [
                    'src' => 'https://blog.com/media/image.png'
                ]
            ],
            [
                'type' => 'text',
                'text' => 'Image',
                'marks' => [
                    [
                        'type' => 'link',
                        'attrs' => [
                            'href' => 'https://blog.com/media/image.png'
                        ]
                    ]
                ]
            ],
            [
                'type' => 'audio',
                'attrs' => [
                    'src' => 'https://blog.com/media/audio.mp3'
                ]
            ]
        ]
    ];

    $contentUnsavedDoc = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'image',
                'attrs' => [
                    'src' => 'https://blog.com/media/image.png'
                ]
            ],
        ]
    ];

    $blog = blogWithAccessLanguageAndRoutes();
    $post = addPost($blog,  [], [
        'content' => json_encode($contentDoc),
        'content_unsaved' => json_encode($contentUnsavedDoc),
    ]);

    $postAnotherBlog = addPost(blogWithAccessLanguageAndRoutes(), [], [
        'content' => json_encode($contentDoc),
    ]);

    $job = new UpdateMediaUrlsInPostsJob(
        $blog,
        'https://blog.com/media/image.png',
        'https://blog.com/media/image-2.png'
    );
    $job->handle();

    $postContent = json_decode($post->variants[0]->content, true);

    expect($postContent['content'][0]['attrs']['src'])->toBe('https://blog.com/media/image-2.png');
    expect($postContent['content'][1]['marks'][0]['attrs']['href'])->toBe('https://blog.com/media/image.png');
    expect($postContent['content'][2]['attrs']['src'])->toBe('https://blog.com/media/audio.mp3');

    $postContentUnsaved = json_decode($post->variants[0]->content_unsaved, true);
    expect($postContentUnsaved['content'][0]['attrs']['src'])->toBe('https://blog.com/media/image-2.png');

    $postAnotherBlogContent = json_decode($postAnotherBlog->variants[0]->content, true);
    expect($postAnotherBlogContent['content'][0]['attrs']['src'])->toBe('https://blog.com/media/image.png');

});
