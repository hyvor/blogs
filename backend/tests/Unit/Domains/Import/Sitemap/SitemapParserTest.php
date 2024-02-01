<?php declare(strict_types=1);

namespace Tests\Unit\Domains\Import\Sitemap;

use App\Domains\Import\Importer\ParserException;
use App\Domains\Import\ImportException;
use App\Domains\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Domains\Import\Sitemap\SitemapParser;
use Illuminate\Support\Facades\Http;

it('parses sitemaps', function() {

    Http::fake([
        'https://example.com/sitemap.txt' => Http::response(<<<TXT
            https://example.com/page1
            https://example.com/page2
            wrong URL
        TXT),

        'https://example.com/page1' => Http::response(<<<HTML
            <title>Page 1</title>
            <meta name="description" content="Page 1 description">
            <article><p>Page 1</p></article>
        HTML),

        'https://example.com/page2' => Http::response(<<<HTML
            <title>Page 2</title>
            <meta name="description" content="Page 2 description">
            <article><p>Page 2</p></article>
        HTML)
    ]);

    $blog = blog();

    $parser = new SitemapParser(
        $blog,
        'https://example.com/sitemap.txt',
        new PageScraperOptions(
            contentSelector: 'article',
        )
    );
    $parser->parse();

    expect($parser->posts)->toHaveCount(2);
    expect($parser->posts[0]->variants[0]->title)->toBe('Page 1');
    expect($parser->posts[1]->variants[0]->title)->toBe('Page 2');

});

it('parses XML sitemaps', function() {

    $xml = <<<XML
        <urlset 
            xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
            xmlns:xhtml="http://www.w3.org/1999/xhtml" 
        >
            <url>
                <loc>https://example.com/page1</loc>
            </url>
            <url>
                <loc>https://example.com/page2</loc>
            </url>
            <url>
                <loc>invalido url</loc>
            </url>
        </urlset>
    XML;
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;

    Http::fake([
        'https://example.com/sitemap.txt' => Http::response($xml, 200, [
            'Content-Type' => 'application/xml'
        ]),

        'https://example.com/page1' => Http::response(<<<HTML
            <title>Page 1</title>
            <meta name="description" content="Page 1 description">
            <article><p>Page 1</p></article>
        HTML),

        'https://example.com/page2' => Http::response(<<<HTML
            <title>Page 2</title>
            <meta name="description" content="Page 2 description">
            <article><p>Page 2</p></article>
        HTML)
    ]);

    $blog = blog();

    $parser = new SitemapParser(
        $blog,
        'https://example.com/sitemap.txt',
        new PageScraperOptions(
            contentSelector: 'article',
        )
    );
    $parser->parse();

    expect($parser->posts)->toHaveCount(2);
    expect($parser->posts[0]->variants[0]->title)->toBe('Page 1');
    expect($parser->posts[1]->variants[0]->title)->toBe('Page 2');

});

it('parses XML without header and without other namespaces', function() {

    $xml = <<<XML
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        <url><loc>https://example.com/page1</loc></url>
        <url><loc>https://example.com/page2</loc></url>
    </urlset>
    XML;
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;

    Http::fake([
        'https://example.com/sitemap.xml' => Http::response($xml, 200),
        'https://example.com/page1' => Http::response(<<<HTML
            <title>Page 1</title>
            <meta name="description" content="Page 1 description">
            <article><p>Page 1</p></article>
        HTML),
        'https://example.com/page2' => Http::response(<<<HTML
            <title>Page 2</title>
            <meta name="description" content="Page 2 description">
            <article><p>Page 2</p></article>
        HTML)
    ]);

    $blog = blog();

    $parser = new SitemapParser(
        $blog,
        'https://example.com/sitemap.xml',
        new PageScraperOptions(
            contentSelector: 'article',
        )
    );
    $parser->parse();

    expect($parser->posts)->toHaveCount(2);
    expect($parser->posts[0]->variants[0]->title)->toBe('Page 1');
    expect($parser->posts[1]->variants[0]->title)->toBe('Page 2');

})->skip(); // TODO: this is failing when running all tests, but not when running only this test

it('error handling when fetchin fails', function() {

    Http::fake([
        'https://example.com/sitemap.txt' => Http::response('https://example.com/page1'),
        'https://example.com/page1' => Http::response('', 404),
    ]);

    $blog = blog();

    $parser = new SitemapParser(
        $blog,
        'https://example.com/sitemap.txt',
        new PageScraperOptions(
            contentSelector: 'article',
        )
    );
    $parser->parse();

})->throws(ParserException::class, 'Error cannot_fetch while scraping https://example.com/page1');