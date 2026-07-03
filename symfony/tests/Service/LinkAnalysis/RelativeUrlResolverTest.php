<?php

namespace App\Tests\Service\LinkAnalysis;

use App\Service\LinkAnalysis\RelativeUrlResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelativeUrlResolver::class)]
class RelativeUrlResolverTest extends TestCase
{
    private RelativeUrlResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new RelativeUrlResolver();
    }

    public function test_passes_through_absolute_http_url(): void
    {
        $result = $this->resolver->resolve('http://example.com/path', 'https://blog.example.com/post');
        $this->assertSame('http://example.com/path', $result);
    }

    public function test_passes_through_absolute_https_url(): void
    {
        $result = $this->resolver->resolve('https://example.com/path', 'https://blog.example.com/post');
        $this->assertSame('https://example.com/path', $result);
    }

    public function test_resolves_root_relative_url(): void
    {
        $result = $this->resolver->resolve('/about', 'https://blog.example.com/post');
        $this->assertSame('https://blog.example.com/about', $result);
    }

    public function test_resolves_anchor_as_fragment(): void
    {
        // anchors are resolved as fragments — filtering happens in PostVariantAnalyzer
        $result = $this->resolver->resolve('#anchor', 'https://blog.example.com/post');
        $this->assertSame('https://blog.example.com/post#anchor', $result);
    }

    #[TestWith(['ftp://example.com/file'])]
    #[TestWith(['mailto:user@example.com'])]
    #[TestWith(['data:text/plain;base64,SGVsbG8='])]
    public function test_returns_null_for_non_http_schemes(string $url): void
    {
        $result = $this->resolver->resolve($url, 'https://blog.example.com/post');
        $this->assertNull($result);
    }
}
