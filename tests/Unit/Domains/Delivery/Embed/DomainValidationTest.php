<?php

namespace Tests\Unit\Domains\Delivery\Embed;

use App\Domains\Delivery\Embed\EmbedService;

it('validates test', function() {

    $blog = blog();

    $blog->setMeta('embedding_domains', '*');
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://hyvor.com'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://hyvor.com/page'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://example.com/page'))->toBeTrue();

    $blog->setMeta('embedding_domains', 'hyvor.com');
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://hyvor.com'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://hyvor.com/page'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://example.com/page'))->toBeFalse();

    $blog->setMeta('embedding_domains', 'hyvor.com, example.com');

    expect(EmbedService::validateEmbeddingUrl($blog, 'https://hyvor.com'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://hyvor.com/page'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://example.com'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://example.com/page'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://another.com'))->toBeFalse();

    $blog->setMeta('embedding_domains', 'https://hyvor.com');
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://hyvor.com'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://hyvor.com/page'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://example.com'))->toBeFalse();


    $blog->setMeta('embedding_domains', 'localhost:8080');
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://localhost:8080/name'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://localhost/page'))->toBeTrue();
    expect(EmbedService::validateEmbeddingUrl($blog, 'https://example.com'))->toBeFalse();
});