<?php

namespace Tests\Unit\Domains\Import;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Import\Importer\Importer;
use App\Domains\Import\Importer\ImportingPost;
use App\Domains\Import\Importer\ImportingPostVariant;
use App\Domains\Import\Importer\ParserAbstract;
use App\Domains\Media\MediaRepository;
use App\Domains\Post\Content\Nodes\Audio\Audio;
use App\Domains\Post\Content\Nodes\Image\Image;
use App\Domains\Post\Content\PostContentService;

it('imports local files', function() {

    $blog = blogWithLanguageAndRoutes();


    $importer = new Importer(
        $blog,
        new class extends ParserAbstract {

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
                                'src' => 'file://' . __DIR__ . '/example.txt',
                                'alt' => 'Image',
                            ]
                        ],
                        [
                            'type' => 'audio',
                            'attrs' => [
                                'src' => 'file://' . __DIR__ . '/example.audio.txt',
                            ]
                        ]
                    ]
                ]);

                $this->addPost(
                    new ImportingPost(
                        publishedAt: now(),
                        featuredImageUrl: 'file://' . __DIR__ . '/example.featured.txt',
                        variants: [
                            new ImportingPostVariant(
                                slug: 'slug',
                                content: $content,
                                title: 'title',
                                description: 'description',
                            )
                        ]
                    )
                );

            }
        },
        true
    );
    $importer->import();
    
    expect($blog->posts()->count())->toBe(1);

    $post = $blog->posts()->first();

    $featuredImage = $post->featured_image_url;
    $name = basename($featuredImage);
    $content = MediaRepository::getContents(MediaRepository::getByBlogIdAndName($blog->id, $name));
    expect($content)->toBe('Featured image');

    $variant = $post->variants()->first();

    $doc = PostContentService::getDocumentFromJson($variant->content, $blog);
    $image = $doc->getNodes(Image::class)[0];
    $name = basename($image->attrs->src);
    $content = MediaRepository::getContents(MediaRepository::getByBlogIdAndName($blog->id, $name));
    expect($content)->toBe('Image');

    $audio = $doc->getNodes(Audio::class)[0];
    $name = basename($audio->attrs->src);
    $content = MediaRepository::getContents(MediaRepository::getByBlogIdAndName($blog->id, $name));
    expect($content)->toBe('Audio file');

});