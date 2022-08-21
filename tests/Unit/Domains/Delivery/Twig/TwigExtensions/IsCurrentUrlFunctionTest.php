<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

test('is_current_url', function () {
    $baseUrl = 'https://myblog.com';
    testTwigRendering(
        "{% if (is_current_url('$baseUrl/page')) %}yes{% endif %}",
        [
            '_blog' => [
                'base_url' => $baseUrl
            ],
            '_meta' => [
                'url' => "$baseUrl/page"
            ]
        ],
        "yes"
    );
});

test('is_current_url with wrong path', function () {
    $baseUrl = 'https://myblog.com';
    testTwigRendering(
        "{% if (is_current_url('$baseUrl/page')) %}yes{% endif %}",
        [
            '_blog' => [
                'base_url' => $baseUrl
            ],
            '_meta' => [
                'url' => "$baseUrl/wrong-page"
            ]
        ],
        ""
    );
});

test('is_current_url with wrong domain', function () {
    $baseUrl = 'https://myblog.com';
    testTwigRendering(
        "{% if (is_current_url('$baseUrl/page')) %}yes{% endif %}",
        [
            '_blog' => [
                'base_url' => $baseUrl
            ],
            '_meta' => [
                'url' => "https://otherdomain.com/page"
            ]
        ],
        ""
    );
});

test('is_current_url with relative URL', function () {
    $baseUrl = 'https://myblog.com';
    testTwigRendering(
        "{% if (is_current_url('page')) %}yes{% endif %}",
        [
            '_blog' => [
                'base_url' => $baseUrl
            ],
            '_meta' => [
                'url' => "$baseUrl/page"
            ]
        ],
        "yes"
    );
});

test('is_current_url with wrong relative URL', function () {
    $baseUrl = 'https://myblog.com';
    testTwigRendering(
        "{% if (is_current_url('page2')) %}yes{% endif %}",
        [
            '_blog' => [
                'base_url' => $baseUrl
            ],
            '_meta' => [
                'url' => "$baseUrl/page"
            ]
        ],
        ""
    );
});
