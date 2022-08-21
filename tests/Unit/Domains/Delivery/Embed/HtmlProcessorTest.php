<?php

namespace Tests\Unit\Domains\Delivery\Embed;

use App\Domains\Delivery\Embed\HtmlProcessor;
use App\Domains\Route\PermalinkRepository;

beforeEach(function () {
    $this->parentUrl = 'https://example.com/blog';
});

it('adds the embed script', function () {
    $html = <<<HTML
    <html>
    <head></head>
    <body></body>
    </html>
    HTML;

    $content = (new HtmlProcessor(blog(), $this->parentUrl, $html))->get();

    expect($content)->toContain('<script', 'src="', '</script>');
    expect($content)->toContain('<style>', '</style>', 'overflow: hidden');
});

it('converts URLs to embed-type URLs in <a>s', function () {
    $baseUrl = PermalinkRepository::getBaseUrl(blog());
    $html = <<<HTML
    <html>
    <head></head>
    <body>
        <a href="$baseUrl/test"></a>
        <a href="$baseUrl/media/image.png"></a>
        <a href="$baseUrl/assets/image.png"></a>
        <a href="/relative"></a>
        <a href="relative"></a>
        <a href="$baseUrl"></a>
        <a href="$baseUrl"></a>
        <a href="https://example.org/test"></a>
    </body>
    </html>
    HTML;

    $content = (new HtmlProcessor(blog(), $this->parentUrl, $html))->get();

    expect($content)->toContain("<a href=\"$this->parentUrl?p=test\"></a>");
    expect($content)->toContain("<a href=\"$this->parentUrl\"></a>");
    expect($content)->toContain("<a href=\"$baseUrl/media/image.png\"></a>");
    expect($content)->toContain("<a href=\"$baseUrl/assets/image.png\"></a>");
    expect($content)->toContain("<a href=\"$this->parentUrl?p=relative\"></a>");
    expect($content)->toContain("<a href=\"relative\"></a>");
    expect($content)->toContain("<a href=\"https://example.org/test\"></a>");
});

it('converts path style', function () {
    $baseUrl = PermalinkRepository::getBaseUrl(blog());
    $html = <<<HTML
    <html>
    <head></head>
    <body>
        <a href="$baseUrl/test"></a>
        <a href="/relative"></a>
    </body>
    </html>
    HTML;

    $content = (new HtmlProcessor(blog(), $this->parentUrl, $html, true))->get();

    expect($content)->toContain("<a href=\"$this->parentUrl/test\"></a>");
    expect($content)->toContain("<a href=\"$this->parentUrl/relative\"></a>");
});
