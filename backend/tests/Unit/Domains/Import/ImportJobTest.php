<?php declare(strict_types=1);

use App\Data\Enums\JobStatusEnum;
use App\Data\Enums\PostStatusEnum;
use App\Domains\Import\Importer\ImportingPost;
use App\Domains\Import\Importer\ImportingPostVariant;
use App\Domains\Import\Importer\ImportJob;
use App\Domains\Import\Importer\ParserAbstract;
use App\Domains\Media\MediaRepository;
use App\Models\Import;
use App\Models\Media;
use Carbon\Carbon;

it('imports posts and uploads images', function() {

    $this->mock(MediaRepository::class, function ($mock) {
        $mock->shouldReceive('uploadFromUrl')
            ->times(3)
            ->andReturn(
                new Media(['name' => 'image.jpg']),
                new Media(['name' => 'default.png']),
                new Media(['name' => 'audio.mp3'])
            );
    });

    $publishTime = now();

    $blog = blogWithLanguage();

    $import = Import::factory()->create([
        'blog_id' => $blog
    ]);

    $job = new ImportJob(
        $blog,
        $import,
        new class($publishTime) extends ParserAbstract {

            public function __construct(
                private readonly Carbon $publishTime
            ) {}

            public function parse(): void
            {

                $content = json_encode([
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Hello World',
                                ]
                            ]
                        ],
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/default.png',
                                'alt' => 'Image',
                            ]
                        ],
                        [
                            'type' => 'audio',
                            'attrs' => [
                                'src' => 'https://example.com/audio.mp3',
                            ]
                        ]
                    ]
                ]);


                $this->addPost(
                    new ImportingPost(
                        publishedAt: $this->publishTime,
                        isPage: false,
                        isFeatured: true,
                        featuredImageUrl: 'https://example.com/image.jpg',
                        variants: [
                            new ImportingPostVariant(
                                slug: 'slug',
                                content: $content,
                                title: 'title',
                                description: 'description',
                                status: PostStatusEnum::PUBLISHED
                            )
                        ]
                    )
                );
            }
        },
        true
    );
    $job->handle();

    expect($blog->posts()->count())->toBe(1);

    $post = $blog->posts()->first();

    expect($post->published_at->toDateTimeString())->toBe($publishTime->toDateTimeString());
    expect($post->featured_image_url)->toBe($blog->url() . '/media/image.jpg');
    expect($post->is_page)->toBe(false);
    expect($post->is_featured)->toBe(true);

    expect($post->variants()->count())->toBe(1);

    $variant = $post->variants()->first();

    expect($variant->slug)->toBe('slug');
    expect($variant->title)->toBe('title');
    expect($variant->description)->toBe('description');

    // uploads image locally
    expect($variant->content)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Hello World',
                    ]
                ]
            ],
            [
                'type' => 'image',
                'attrs' => [
                    'src' => $blog->url() . '/media/default.png',
                    'alt' => 'Image',
                    'width' => null,
                    'height' => null,
                ]
            ],
            [
                'type' => 'audio',
                'attrs' => [
                    'src' => $blog->url() . '/media/audio.mp3',
                ]
            ]
        ]
    ]));

    $import->refresh();
    expect($import->status)->toBe(JobStatusEnum::COMPLETED);
    expect($import->posts_count)->toBe(1);

});