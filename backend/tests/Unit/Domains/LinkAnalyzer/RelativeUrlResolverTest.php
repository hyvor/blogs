<?php

namespace Tests\Unit\Domains\LinkAnalyzer;

use App\Domains\LinkAnalyzer\RelativeUrlResolver;
use PHPUnit\Framework\TestCase;

class RelativeUrlResolverTest extends TestCase
{
    public function testGetsFullUrl(): void
    {
        // http external
        $this->assertEquals(
            'http://example.com',
            RelativeUrlResolver::resolve('http://example.com', 'https://blog.com/my-post')
        );

        // https external
        $this->assertEquals(
            'https://example.com',
            RelativeUrlResolver::resolve('https://example.com', 'https://blog.com/my-post')
        );

        // relative
        $this->assertEquals(
            'https://blog.com/about',
            RelativeUrlResolver::resolve('about', 'https://blog.com/my-post')
        );

        // two levels relative
        $this->assertEquals(
            'https://blog.com/blog/about',
            RelativeUrlResolver::resolve('about', 'https://blog.com/blog/my-post')
        );

        // relative with /
        $this->assertEquals(
            'https://blog.com/about',
            RelativeUrlResolver::resolve('/about', 'https://blog.com/my-post')
        );

        // relative two folders
        $this->assertEquals(
            'https://blog.com/about',
            RelativeUrlResolver::resolve('../about', 'https://blog.com/blog/my-post')
        );

        // empty
        $this->assertEquals('https://blog.com/my-post', RelativeUrlResolver::resolve('', 'https://blog.com/my-post'));

        // empty with /
        $this->assertEquals('https://blog.com/', RelativeUrlResolver::resolve('/', 'https://blog.com/my-post'));

        // ftp
        $this->assertNull(RelativeUrlResolver::resolve('ftp://example.com', 'https://blog.com/my-post'));

        // data
        $this->assertNull(RelativeUrlResolver::resolve('data:image/png;base64,ABC', 'https://blog.com/my-post'));

        // mailto
        $this->assertNull(RelativeUrlResolver::resolve('mailto:s@hyvor.com', 'https://blog.com/my-post'));

        // rare case: post draft without slug
        $this->assertEquals('https://blog.com/about', RelativeUrlResolver::resolve('about', 'https://blog.com'));
    }
}