<?php

namespace Tests\Unit\Domains\Delivery\Response\Sitemap;

use App\Domains\Delivery\Processors\Sitemap\UrlPostEntry;
use App\Domains\Route\PermalinkRepository;

it('works', function () {
    $blog = blog();
    $post = aPublishedPost();
    $basePath = PermalinkRepository::getFullUrlFromPath($blog);
    $slug = $post->slug;

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
                                'src' => 'https://example.com/image.png',
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
    expect($xml)->toContain("<xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"$basePath/$slug\" />");
    expect($xml)->toContain("<xhtml:link rel=\"alternate\" hreflang=\"fr\" href=\"$basePath/fr/$slug\" />");

    expect($xml)->toContain("<image:image><image:loc>$basePath/image.png</image:loc></image:image>");
    expect($xml)->toContain("<image:image><image:loc>$basePath/image2.png</image:loc></image:image>");
    expect($xml)->not->toContain('<image:image><image:loc>https://example.com/image.png</image:loc></image:image>');
});
