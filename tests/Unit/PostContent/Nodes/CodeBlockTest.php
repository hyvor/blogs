<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentRepository;
use DOMDocument;

test('json to HTML', function () {
    $code = '$x = null';

    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'code_block',
                'attrs' => [
                    'language' => 'php',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $code
                    ]
                ]
            ]
        ]
    ]);

    $html = PostContentRepository::getHtml($json, blog());

    $dom = new DOMDocument();
    $dom->loadXML($html);

    // <pre>
    $pre = $dom->firstChild;
    $this->assertEquals("language-php", $pre->attributes->getNamedItem('class')->value);

    // <code>
    $code = $pre->firstChild;
    expect($code->nodeName)->toBe('code');

});
