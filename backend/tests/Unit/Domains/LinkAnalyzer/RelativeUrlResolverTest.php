<?php

namespace Tests\Unit\Domains\LinkAnalyzer;

use App\Domains\LinkAnalyzer\RelativeUrlResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelativeUrlResolver::class)]
class RelativeUrlResolverTest extends TestCase
{
    public function testGetsFullUrl(): void
    {
        // http external
        $this->assertSame(
            'http://example.com',
            RelativeUrlResolver::resolve('http://example.com', 'https://blog.com/my-post')
        );

        // https external
        $this->assertSame(
            'https://example.com',
            RelativeUrlResolver::resolve('https://example.com', 'https://blog.com/my-post')
        );

        // relative
        $this->assertSame(
            'https://blog.com/about',
            RelativeUrlResolver::resolve('about', 'https://blog.com/my-post')
        );

        // two levels relative
        $this->assertSame(
            'https://blog.com/blog/about',
            RelativeUrlResolver::resolve('about', 'https://blog.com/blog/my-post')
        );

        // relative with /
        $this->assertSame(
            'https://blog.com/about',
            RelativeUrlResolver::resolve('/about', 'https://blog.com/my-post')
        );

        // relative two folders
        $this->assertSame(
            'https://blog.com/about',
            RelativeUrlResolver::resolve('../about', 'https://blog.com/blog/my-post')
        );

        // empty
        $this->assertSame('https://blog.com/my-post', RelativeUrlResolver::resolve('', 'https://blog.com/my-post'));

        // empty with /
        $this->assertSame('https://blog.com/', RelativeUrlResolver::resolve('/', 'https://blog.com/my-post'));

        // ftp
        $this->assertNull(RelativeUrlResolver::resolve('ftp://example.com', 'https://blog.com/my-post'));

        // data
        $this->assertNull(RelativeUrlResolver::resolve('data:image/png;base64,ABC', 'https://blog.com/my-post'));

        // mailto
        $this->assertNull(RelativeUrlResolver::resolve('mailto:s@hyvor.com', 'https://blog.com/my-post'));

        // rare case: post draft without slug
        $this->assertSame('https://blog.com/about', RelativeUrlResolver::resolve('about', 'https://blog.com'));

        // bug: URL with a space
        $this->assertSame(
            'https://twitter.com',
            RelativeUrlResolver::resolve(' https://twitter.com', 'https://blog.com')
        );

        // malformed URL
        $this->assertNull(RelativeUrlResolver::resolve('http://example.com:80:80', 'https://blog.com'));
    }
}