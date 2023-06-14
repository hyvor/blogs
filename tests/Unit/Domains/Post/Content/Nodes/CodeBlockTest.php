<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Domains\Post\Content\PostContentOptions;
use App\Domains\Post\Content\PostContentService;
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
                    'annotations' => 'h=1',
                    'name' => 'file.js'
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $code,
                    ],
                ],
            ],
        ],
    ]);

    $html = PostContentService::getHtml($json, blog());

    $dom = new DOMDocument();
    $dom->loadXML($html);

    // <pre>
    $pre = $dom->firstChild;

    expect($pre->attributes->getNamedItem('class')->value)->toBe('language-php has-highlight has-line-numbers');
    expect($pre->attributes->getNamedItem('data-annotations')->value)->toBe('h=1');
    expect($pre->attributes->getNamedItem('data-name')->value)->toBe('file.js');
    expect($pre->attributes->getNamedItem('data-language')->value)->toBe('php');

    // <code>
    $code = $pre->firstChild;
    expect($code->nodeName)->toBe('code');
});

test('json to HTML with is_plain', function() {

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
                        'text' => $code,
                    ],
                ],
            ],
        ],
    ]);

    $html = PostContentService::getHtml($json, blog(), new PostContentOptions(
        isCodeBlockPlain: true
    ));

    expect($html)->toContain('<code>$x = null</code>');

});

test('HTML to JSON', function () {
    $name = 'app.php';
    $content = '$x = null';
    $annotations = 'h=1';

    $html = "<pre class=\"language-php\" data-language=\"php\" data-name=\"$name\" data-annotations=\"$annotations\">$content</pre>";

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)
        ->toEqual(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'code_block',
                    'attrs' => [
                        'language' => 'php',
                        'name' => $name,
                        'annotations' => $annotations,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                        ],
                    ],
                ],
            ],
        ]));
});
