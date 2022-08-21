<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

test('pagination_page_url', function () {
    $baseUrl = 'https://myblog.com';
    testTwigRendering(
        '{{ 1 | pagination_page_url }}',
        [
            '_meta' => [
                'url' => $baseUrl
            ]
        ],
        "$baseUrl"
    );

    testTwigRendering(
        '{{ 1 | pagination_page_url }}',
        [
            '_meta' => [
                'url' => $baseUrl . '/page/2'
            ]
        ],
        "$baseUrl"
    );

    testTwigRendering(
        '{{ 2 | pagination_page_url }}',
        [
            '_meta' => [
                'url' => $baseUrl
            ]
        ],
        "$baseUrl/page/2"
    );

    testTwigRendering(
        '{{ 2 | pagination_page_url }}',
        [
            '_meta' => [
                'url' => "$baseUrl/page/3"
            ]
        ],
        "$baseUrl/page/2"
    );
});
