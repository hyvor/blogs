<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Domains\Delivery\Twig\TwigExtensions;

it('gets rich schema', function () {
    testTwigRendering(
        "{{ get_rich_schema() }}",
        [
            '_meta' => [
                'title' => 'Some Title',
                'featured_image' => 'https://example.com/image.jpg',
            ],
            '_post' => [
                'published_at' => '2024-11-06 12:10:20',
                'updated_at' => '2024-11-06 12:10:20',
                'authors' => [
                    [
                        'name' => 'John Doe',
                        'email' => 'johndoe1@email.com'
                    ],
                    [
                        'name' => 'Jane Doe',
                        'email' => 'janedoe@email.com'
                    ]
                ]
            ]
        ],
        'hi'
    );
//    expect(TwigExtensions::getRichSchema(['https://example.com']))->toBe('hi');

});
