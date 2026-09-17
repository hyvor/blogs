<?php

namespace App\Tests\Api\Data;

use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class DataApiCorsListenerTest extends ApiTestCase
{
    public function test_get_request_has_cors_header(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage(variants: false);

        $this->client->request(
            'GET',
            '/api/data/v0/' . $blog->getSubdomain() . '/blog',
            server: ['HTTP_ORIGIN' => 'https://example.com'],
        );

        $this->assertResponseIsSuccessful();
        // NelmioCorsBundle reflects the request's Origin back rather than a literal '*'
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', 'https://example.com');
    }

    public function test_preflight_request_is_handled(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage(variants: false);

        $this->client->request(
            'OPTIONS',
            '/api/data/v0/' . $blog->getSubdomain() . '/blog',
            server: [
                'HTTP_ORIGIN' => 'https://example.com',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
            ],
        );

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', 'https://example.com');
        $this->assertResponseHeaderSame('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $this->assertResponseHeaderSame('Access-Control-Max-Age', '86400');
    }

    public function test_console_api_does_not_get_cors_header(): void
    {
        $this->consoleOrgApi('GET', '/ping', server: ['HTTP_ORIGIN' => 'https://example.com']);

        $this->assertFalse($this->client->getResponse()->headers->has('Access-Control-Allow-Origin'));
    }
}
