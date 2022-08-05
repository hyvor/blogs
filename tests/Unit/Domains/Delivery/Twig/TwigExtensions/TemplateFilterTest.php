<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

test('template filter', function() {

    $data = [
        'var1' => '{{ var2 }}',
        'var2' => 'test'
    ];

    // without template
    testTwigRendering(
        '{{ var1 }}',
        $data,
        '{{ var2 }}'
    );

    // with template
    testTwigRendering(
        '{{ var1 | template }}',
        $data,
        'test'
    );

});