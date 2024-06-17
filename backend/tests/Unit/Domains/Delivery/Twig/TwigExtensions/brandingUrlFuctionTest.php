<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

dataset('brandingUrls', [
    ['https://myblog.com', 'https://blogs.hyvor.com?source=branding&ref=myblog.com'],
    ['https://myblog.com/', 'https://blogs.hyvor.com?source=branding&ref=myblog.com'],
    ['www.myblog.com', 'https://blogs.hyvor.com?source=branding&ref='],
    ['someone@myblog.com', 'https://blogs.hyvor.com?source=branding&ref='],
]);

test('branding_url', function ($baseUrl, $expected) {
    testTwigRendering(
        "{{ branding_url() }}",
        [
            '_blog' => [
                'base_url' => $baseUrl
            ],
        ],
        $expected
    );
})->with('brandingUrls');