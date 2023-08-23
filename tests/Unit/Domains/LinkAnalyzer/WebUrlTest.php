<?php declare(strict_types=1);

namespace Tests\Unit\Domains\LinkAnalyzer;

use App\Domains\LinkAnalyzer\AnalyzeAllLinks;
use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\Marks\LinkAttrs;
use Hyvor\Phrosemirror\Document\Mark;

it('detects web URL', function($url, $expected) {

    $blog = blog();
    $baseUrl = 'https://example.com';
    $mark = new Mark(
        new Link($blog),
        LinkAttrs::fromArray([
            'href' => $url,
        ])
    );

    expect(AnalyzeAllLinks::getWebUrlFromLinkMark($mark, $baseUrl))->toBe($expected);

})->with([
    ['http://example.com', 'http://example.com'],
    ['https://example.com', 'https://example.com'],
    ['about', 'https://example.com/about'],
    ['/about', 'https://example.com/about'],
    ['', 'https://example.com'],
    ['http://hyvor.com', 'http://hyvor.com'],
    ['https://hyvor.com', 'https://hyvor.com'],
    ['ftp://hyvpr.com', null],
    ['data:image/png;base64,ABC', null],
    ['mailto:test@hyvor.com', null],
    ['javascript:alert("hello")', null]
]);