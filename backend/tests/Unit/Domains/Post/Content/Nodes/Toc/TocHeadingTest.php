<?php

namespace Tests\Unit\Domains\Post\Content\Nodes\Toc;

use App\Domains\Post\Content\Nodes\Toc\TocHeading;
use App\Domains\Post\Content\PostContentService;

it('gets from node', function() {

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 1,
                    'id' => 'my-big-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My big heading',
                    ],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 2,
                    'id' => 'my-small-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My small heading',
                    ],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 3,
                    'id' => 'my-tiny-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My tiny heading',
                    ],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => [
                    'level' => 4,
                    'id' => 'my-tiny-tiny-heading',
                ],
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'My tiny tiny heading',
                    ],
                ],
            ]
        ]
    ];

    $node = PostContentService::getDocumentFromJson($json, blog());
    $headings = TocHeading::fromNode($node, [1,2,3]);

    expect($headings)->toBeArray();
    expect($headings)->toHaveCount(3);

    expect($headings[0]->title)->toBe('My big heading');
    expect($headings[0]->id)->toBe('my-big-heading');
    expect($headings[0]->level)->toBe(1);

    expect($headings[1]->title)->toBe('My small heading');
    expect($headings[1]->id)->toBe('my-small-heading');
    expect($headings[1]->level)->toBe(2);

    expect($headings[2]->title)->toBe('My tiny heading');
    expect($headings[2]->id)->toBe('my-tiny-heading');
    expect($headings[2]->level)->toBe(3);


});

it('gets from html', function() {

    $html = <<<HTML
        <h1 id="my-big-heading">My big heading</h1>
        <h2 id="my-small-heading">My small heading</h2>
        <h3 id="my-tiny-heading">My tiny heading</h3>
        <h4 id="my-tiny-tiny-heading">My tiny tiny heading</h4>
    HTML;

    $headings = TocHeading::fromHtml($html, [1,2,3]);

    expect($headings)->toBeArray();
    expect($headings)->toHaveCount(3);

    expect($headings[1]->title)->toBe('My small heading');
    expect($headings[1]->id)->toBe('my-small-heading');
    expect($headings[1]->level)->toBe(2);

    expect($headings[2]->title)->toBe('My tiny heading');
    expect($headings[2]->id)->toBe('my-tiny-heading');
    expect($headings[2]->level)->toBe(3);

});

it('when invalid html', function() {

    // well, this is stil not consired invalid by DOMDocument

    $invalidHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Invalid HTML</title>
</head>
<body>
    <div>
        <p>This is a paragraph with no closing tag.
    </div>
    <p>Another paragraph with no opening tag.</p>
</body>
</html>
HTML;

    $headings = TocHeading::fromHtml($invalidHtml, [1,2,3]);

    expect($headings)->toBe([]);

});

it('levels test', function() {

    expect(TocHeading::getLevels([1,2,3]))->toBe([1,2,3]);
    expect(TocHeading::getLevels([1,2,3,4]))->toBe([1,2,3,4]);
    expect(TocHeading::getLevels([-1, 0,1,2,3,4,5,6,7]))->toBe([1,2,3,4,5,6]);
    expect(TocHeading::getLevels(['1', '2 ', 3]))->toBe([1,2,3]);

    // string
    expect(TocHeading::getLevels('1,2,3'))->toBe([1,2,3]);
    expect(TocHeading::getLevels('1,2,3,4,6,7'))->toBe([1,2,3,4,6]);
    expect(TocHeading::getLevels('1,2,3|4,5,6,7'))->toBe([1,2,3,5,6]);
    expect(TocHeading::getLevels('1|2|3'))->toBe([1]);

    // null
    expect(TocHeading::getLevels(null))->toBe([1,2,3,4,5,6]);

});

# bug - unicode chars problem
# https://davidwalsh.name/domdocument-utf8-problem didn't work
# https://stackoverflow.com/a/8218649/9059939
it('html specials', function() {
    // … is a unicode char
    $html = '<h1 id="my-big-heading">Hello Worlding…</h1>';
    $headings = TocHeading::fromHtml($html, [1,2,3]);
    expect($headings[0]->title)->toBe('Hello Worlding…');
});