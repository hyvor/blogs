<?php

namespace Tests\Unit\Domains\Post\Content;

use App\Domains\Post\Content\PostContentService;

it('removes unnecessary whitespaces', function() {

    $html = '<figure>
<img src="https://example.com/image.png" alt="Image" />
<figcaption>
<p><span>Illustrations</span></p>
</figcaption>
</figure>';

    $doc = PostContentService::getJsonFromHtml($html, blog());

    expect($doc)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figure',
                'content' => [
                    [
                        'type' => 'image',
                        'attrs' => [
                            'src' => 'https://example.com/image.png',
                            'alt' => 'Image',
                            'width' => null,
                            'height' => null,
                        ]
                    ],
                    [
                        'type' => 'figcaption',
                        'content' => [
                            ['type' => 'text', 'text' => 'Illustrations']
                        ]
                    ]
                ]
            ]
        ]
    ]));

});