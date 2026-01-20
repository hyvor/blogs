<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Domains\Delivery\Twig\TwigExtensions;

it('gets rich schema', function () {
    testTwigRendering(
        "{{ rich_schema() }}",
        [
            '_meta' => [
                'title' => 'Some Title',
                'featured_image' => 'https://example.com/image.jpg',
            ],
            '_post' => [
                'published_at' => '1732204950',
                'updated_at' => '1732204950',
                'authors' => [
                    [
                        'name' => 'John Doe',
                        'url' => 'johndoe1@email.com'
                    ],
                    [
                        'name' => 'Jane Doe',
                        'url' => ''
                    ],
                    [
                        'name' => 'John Doe 2'
                    ]
                ]
            ]
        ],
        '<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "Some Title",
    "datePublished": "2024-11-21T16:02:30+00:00",
    "dateModified": "2024-11-21T16:02:30+00:00",
    "author": [
        {
            "type": "@Person",
            "name": "John Doe",
            "url": "johndoe1@email.com"
        },
        {
            "type": "@Person",
            "name": "Jane Doe"
        },
        {
            "type": "@Person",
            "name": "John Doe 2"
        }
    ],
    "image": [
        "https://example.com/image.jpg"
    ]
}
</script>'
    );
});

// This was added to prevent template rendering issues for custom
// routes that does not have _meta defined
it('does not get rich schema when _meta is undefined', function () {
    testTwigRendering(
        "{{ rich_schema() }}",
        [],
        ''
    );
});
