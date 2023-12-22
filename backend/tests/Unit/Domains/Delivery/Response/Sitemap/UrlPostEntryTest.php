<?php declare(strict_types=1);

namespace Tests\Unit\Domains\Delivery\Response\Sitemap;

use App\Domains\Delivery\Processors\Sitemap\UrlPostEntry;
use App\Domains\Route\PermalinkRepository;

it('works', function () {
    $blog = blogWithLanguageAndRoutes();
    addLanguage($blog);
    $blog->refresh();
    $post = addPublishedPost($blog);
    $basePath = PermalinkRepository::getFullUrlFromPath($blog);

    $slug = $post->variants[0]->slug;
    $translatedSlug = $post->variants[1]->slug;

    $post->variants[0]->update([
        'content' => json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => $basePath.'/image.png',
                    ],
                ],
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => $basePath.'/image2.png',
                            ],
                        ],
                        // external
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'https://example.com/image.png?n&m',
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $entry = new UrlPostEntry($post);
    $xml = $entry->toXML();

    expect($xml)->toContain("<loc>$basePath/$slug</loc>");
    expect($xml)->toContain("<xhtml:link rel=\"alternate\" hreflang=\"{$blog->languages[0]->code}\" href=\"$basePath/$slug\" />");
    expect($xml)->toContain("<xhtml:link rel=\"alternate\" hreflang=\"{$blog->languages[1]->code}\" href=\"$basePath/{$blog->languages[1]->code}/$translatedSlug\" />");

    expect($xml)->toContain("<image:image><image:loc>$basePath/image.png</image:loc></image:image>");
    expect($xml)->toContain("<image:image><image:loc>$basePath/image2.png</image:loc></image:image>");
    expect($xml)->not->toContain('<image:image><image:loc>https://example.com/image.png?n&amp;m</image:loc></image:image>');
});
