<?php declare(strict_types=1);

namespace Tests\Unit\Domains\Import\Sitemap;

use App\Domains\Import\Sitemap\PageScraper\PageScraper;
use Illuminate\Support\Facades\Http;

it('scrapes title description and content', function() {

    Http::fake([
        'https://example.com/page' => Http::response(<<<HTML
            <html>
                <body>
                    <h1>This is the <b>title</b></h1>
                    <p class="description">
                        So the <i>description</i>
                    </p>
                    <article>
                        <p>
                            Hello World
                        </p>
                    </article>
                </body>
            </html>
        HTML)
    ]);

    $scraper = new PageScraper(
        blog(),
        url: 'https://example.com/page',
        titleSelector: 'h1',
        descriptionSelector: 'p.description',
        contentSelector: 'article'
    );
    $scraper->scrape();

    expect($scraper->content)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello World']
                ]
            ]
        ]
    ]));
    expect($scraper->title)->toBe('This is the title');
    expect($scraper->description)->toBe('So the description');

});

it('scrapes excludes elements from content', function() {

    Http::fake([
        'https://example.com/page' => Http::response(<<<HTML
           
            <article>
                <p>
                    Hello World
                </p>
                <blockquote>
                    This is an ad
                </blockquote>
            </article>
        HTML)
    ]);

    $scraper = new PageScraper(
        blog(),
        url: 'https://example.com/page',
        titleSelector: 'h1',
        descriptionSelector: 'p.description',
        contentSelector: 'article',
        contentExcludeSelector: 'blockquote'
    );
    $scraper->scrape();

    expect($scraper->content)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello World']
                ]
            ]
        ]
    ]));

});

it('converts elements within codeblocks to text', function() {

    Http::fake([
        'https://example.com/page' => Http::response(<<<HTML
            <article>
                <pre><code><span>Test</span><b> code block</b>
        <span>in new line</span></code></pre>
            </article>
        HTML)
    ]);

    $scraper = new PageScraper(
        blog(),
        url: 'https://example.com/page',
        titleSelector: 'h1',
        descriptionSelector: 'p.description',
        contentSelector: 'article',
        contentExcludeSelector: 'blockquote'
    );
    $scraper->scrape();

    expect($scraper->content)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'code_block',
                'attrs' => [
                    'language' => '',
                    'name' => '',
                    'annotations' => ''
                ],
                'content' => [
                    ['type' => 'text', 'text' => "Test code block\nin new line"]
                ]
            ]
        ]
    ]));

});