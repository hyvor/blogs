<?php

namespace App\Tests\Service\LinkAnalysis;

use App\Service\AppConfig;
use App\Service\LinkAnalysis\Dto\StatusResult;
use App\Service\LinkAnalysis\StatusCheck\ExternalLinkStatusCheck;
use App\Service\LinkAnalysis\StatusCheck\IgnoreReason;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(ExternalLinkStatusCheck::class)]
class ExternalStatusCheckTest extends TestCase
{
    /**
     * @param array<string, MockResponse> $mockResponses
     * @return array<string, StatusResult>
     */
    private function check(array $mockResponses): array
    {
        $client = new MockHttpClient($mockResponses);
        $checker = new ExternalLinkStatusCheck($client, new NullLogger(), new AppConfig(version: '1.0', domainApp: 'example.com'));
        return $checker->check(array_keys($mockResponses));
    }

    public function test_returns_status_code_for_successful_response(): void
    {
        $statuses = $this->check([
            'https://example.com' => new MockResponse('OK', ['http_code' => 200]),
        ]);

        $this->assertSame(200, $statuses['https://example.com']->httpStatus);
        $this->assertFalse($statuses['https://example.com']->ignored);
    }

    public function test_returns_0_for_connection_error(): void
    {
        $client = new MockHttpClient([
            new MockResponse('', ['error' => 'Connection refused']),
        ]);
        $checker = new ExternalLinkStatusCheck($client, new NullLogger(), new AppConfig(version: '1.0', domainApp: 'example.com'));
        $statuses = $checker->check(['https://example.com']);

        $this->assertSame(0, $statuses['https://example.com']->httpStatus);
        $this->assertFalse($statuses['https://example.com']->ignored);
    }

    public function test_detects_cloudflare_challenge_as_known_firewall(): void
    {
        $response = new MockResponse('', [
            'http_code' => 403,
            'response_headers' => ['cf-mitigated: challenge'],
        ]);
        $client = new MockHttpClient([$response]);
        $checker = new ExternalLinkStatusCheck($client, new NullLogger(), new AppConfig(version: '1.0', domainApp: 'example.com'));
        $statuses = $checker->check(['https://protected.example.com']);

        $result = $statuses['https://protected.example.com'];
        $this->assertSame(403, $result->httpStatus);
        $this->assertTrue($result->ignored);
        $this->assertSame(IgnoreReason::KNOWN_FIREWALL, $result->ignoreReason);
    }

    public function test_403_without_firewall_headers_is_not_ignored(): void
    {
        $statuses = $this->check([
            'https://example.com' => new MockResponse('Forbidden', ['http_code' => 403]),
        ]);

        $this->assertSame(403, $statuses['https://example.com']->httpStatus);
        $this->assertFalse($statuses['https://example.com']->ignored);
    }

    public function test_returns_404_status_code(): void
    {
        $statuses = $this->check([
            'https://example.com/missing' => new MockResponse('Not Found', ['http_code' => 404]),
        ]);

        $this->assertSame(404, $statuses['https://example.com/missing']->httpStatus);
        $this->assertFalse($statuses['https://example.com/missing']->ignored);
    }
}
