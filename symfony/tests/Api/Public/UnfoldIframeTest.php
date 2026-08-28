<?php

namespace App\Tests\Api\Public;

use App\Api\Public\UnfoldController;
use App\Service\UrlData\UrlDataService;
use App\Tests\Case\ApiTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UnfoldController::class)]
#[CoversClass(UrlDataService::class)]
class UnfoldIframeTest extends ApiTestCase
{
    public function test_returns_privacy_iframe_for_embeddable_url(): void
    {
        $this->client->request(
            'GET',
            '/api/public/unfold/iframe?url=' . urlencode('https://www.youtube.com/watch?v=Z0kGAz6HYM8'),
        );

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertStringContainsString('text/html', (string) $response->headers->get('Content-Type'));

        $content = (string) $response->getContent();
        $this->assertStringContainsString('<iframe', $content);
        // wrapped by Hyvor\Unfold\Embed\Iframe\PrivacyIframe::wrap()
        $this->assertStringContainsString('<script>', $content);
    }

    public function test_returns_fallback_text_for_non_embeddable_url(): void
    {
        $this->client->request(
            'GET',
            '/api/public/unfold/iframe?url=' . urlencode('https://example.com/not-embeddable-page'),
        );

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('This URL cannot be embedded.', $response->getContent());
    }

    public function test_validates_url(): void
    {
        $this->client->request('GET', '/api/public/unfold/iframe?url=not-a-url');

        $response = $this->client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
    }
}
