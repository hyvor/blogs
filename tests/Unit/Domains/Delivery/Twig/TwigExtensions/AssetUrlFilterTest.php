<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

test('asset_url', function() {

    $url = 'https://myblog.com';
    $blogObject = getBlogObject([
        'base_url' => $url
    ]);

    testTwigRendering(
        "{{ 'script.js' | asset_url }}",
        [
            '_blog' => $blogObject
        ],
        "$url/assets/script.js"
    );

});