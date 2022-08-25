<?php

namespace Tests\Unit\Domains\Delivery\Embed;

use App\Domains\Delivery\Embed\EmbedHtmlProcessor;
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

    $content = (new EmbedHtmlProcessor(blog(), $this->parentUrl, $html))->get();

    expect($content)->toContain('<script', 'src="', '</script>');
    expect($content)->toContain('<style>', '</style>', 'overflow: hidden');
});

it('converts anchors to embed-type URLs in <a>s', function () {
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

    $content = (new EmbedHtmlProcessor(blog(), $this->parentUrl, $html))->get();

    expect($content)->toContain("<a href=\"$this->parentUrl?p=test\"></a>");
    expect($content)->toContain("<a href=\"$this->parentUrl\"></a>");
    expect($content)->toContain("<a href=\"$baseUrl/media/image.png\"></a>");
    expect($content)->toContain("<a href=\"$baseUrl/assets/image.png\"></a>");
    expect($content)->toContain("<a href=\"$this->parentUrl?p=relative\"></a>");
    expect($content)->toContain("<a href=\"relative\"></a>");
    expect($content)->toContain("<a href=\"https://example.org/test\"></a>");
});

it('converts links in head to embed-type URLs', function() {

    $baseUrl = PermalinkRepository::getBaseUrl(blog());
    $html = <<<HTML
    <html>
    <head>
        <link rel="canonical" href="$baseUrl/test" />
        <link rel="alternate" href="$baseUrl/alt" />
        <link rel="alternate" href="https://another.com/test" />
        <link rel="wrong" href="$baseUrl/alt" />
    </head>
    <body>
    </body>
    </html>
    HTML;

    $content = (new EmbedHtmlProcessor(blog(), $this->parentUrl, $html))->get();
    expect($content)->toContain("<link rel=\"canonical\" href=\"$this->parentUrl?p=test\">");
    expect($content)->toContain("<link rel=\"alternate\" href=\"$this->parentUrl?p=alt\">");
    // not replaced because
    expect($content)->toContain("<link rel=\"alternate\" href=\"https://another.com/test\">");
    expect($content)->toContain("<link rel=\"wrong\" href=\"$baseUrl/alt\">");

});

it('converts og/twitter URLs to embed-type URLs', function() {

    $baseUrl = PermalinkRepository::getBaseUrl(blog());
    $html = <<<HTML
    <html>
    <head>
        <meta property="og:url" content="$baseUrl/testog" />
        <meta name="twitter:url" content="$baseUrl/testtwitter" />
        <meta name="other" content="$baseUrl/other" />
    </head>
    <body>
    </body>
    </html>
    HTML;

    $content = (new EmbedHtmlProcessor(blog(), $this->parentUrl, $html))->get();
    expect($content)->toContain("<meta property=\"og:url\" content=\"$this->parentUrl?p=testog\">");
    expect($content)->toContain("<meta name=\"twitter:url\" content=\"$this->parentUrl?p=testtwitter\">");
    expect($content)->toContain("<meta name=\"other\" content=\"$baseUrl/other\">");
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

    $content = (new EmbedHtmlProcessor(blog(), $this->parentUrl, $html, true))->get();

    expect($content)->toContain("<a href=\"$this->parentUrl/test\"></a>");
    expect($content)->toContain("<a href=\"$this->parentUrl/relative\"></a>");
});

it('adds Google indexifembedded', function() {

    $html = <<<HTML
    <html>
        <head></head>
        <body></body>
    </html>
    HTML;
    $content = (new EmbedHtmlProcessor(blog(), $this->parentUrl, $html, true))->get();
    expect($content)->toContain('<meta name="googlebot" content="noindex,indexifembedded">');

});
