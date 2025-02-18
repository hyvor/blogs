<?php

namespace Tests\Unit\Domains\LinkAnalyzer\LinkStatusCheck;

use App\Domains\LinkAnalyzer\LinkStatusCheck\ExternalStatusCheck;
use App\Domains\LinkAnalyzer\LinkStatusCheck\IgnoreReasonEnum;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Tests\Case\DatabaseTestCase;

class ExternalStatusCheckTest extends DatabaseTestCase
{

    /**
     * @param array<mixed> $info
     */
    private function getClient(string $body = '', array $info = []): MockHttpClient
    {
        // https://symfony.com/doc/current/http_client.html#testing-network-transport-exceptions
        return new MockHttpClient([
            new MockResponse($body, $info),
        ]);
    }

    public function testCloudflareFirewall(): void
    {
        $client = $this->getClient(info: [
            'http_code' => 401,
            'response_headers' => [
                'CF-mitigated' => 'challenge',
            ],
        ]);

        $service = new ExternalStatusCheck($client);
        $result = $service->check(['https://example.com']);

        $urlResult = $result['https://example.com'];

        $this->assertSame(true, $urlResult->ignored);
        $this->assertSame(IgnoreReasonEnum::KNOWN_FIREWALL, $urlResult->ignoreReason);
        $this->assertSame('firewall: cloudflare_challenge', $urlResult->comment);
    }

}