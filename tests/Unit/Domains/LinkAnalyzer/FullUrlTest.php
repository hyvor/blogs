<?php
namespace Tests\Unit\Domains\LinkAnalyzer;
use App\Domains\LinkAnalyzer\FullUrl;

it('gets full URL', function() {

    // http external
    expect(FullUrl::getFullUrl('http://example.com', 'https://blog.com/my-post'))
        ->toBe('http://example.com');

    // https external
    expect(FullUrl::getFullUrl('https://example.com', 'https://blog.com/my-post'))
        ->toBe('https://example.com');

    // relative
    expect(FullUrl::getFullUrl('about', 'https://blog.com/my-post'))
        ->toBe('https://blog.com/about');

    // two levels relative
    expect(FullUrl::getFullUrl('about', 'https://blog.com/blog/my-post'))
        ->toBe('https://blog.com/blog/about');

    // relative with /
    expect(FullUrl::getFullUrl('/about', 'https://blog.com/my-post'))
        ->toBe('https://blog.com/about');

    // empty
    expect(FullUrl::getFullUrl('', 'https://blog.com/my-post'))
        ->toBe('https://blog.com/my-post');

    // empty with /
    expect(FullUrl::getFullUrl('/', 'https://blog.com/my-post'))
        ->toBe('https://blog.com/');

    // ftp
    expect(FullUrl::getFullUrl('ftp://example.com', 'https://blog.com/my-post'))
        ->toBe(null);

    // data
    expect(FullUrl::getFullUrl('data:image/png;base64,ABC', 'https://blog.com/my-post'))
        ->toBe(null);

    // mailto
    expect(FullUrl::getFullUrl('mailto:s@hyvor.com', 'https://blog.com/my-post'))
        ->toBe(null);

    /**
     * In rare cases (post draft without slug), the URL can be just the domain
     * testing for it
     */
    expect(FullUrl::getFullUrl('about', 'https://blog.com'))
        ->toBe('https://blog.com/about');

});