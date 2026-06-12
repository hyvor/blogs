<?php

namespace App\Tests\Api\Delivery;

use App\Api\Delivery\DeliveryController;
use App\Entity\Blog;
use App\Entity\Enum\ApiKeyType;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use App\Entity\Enum\ThemeFileFolder;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(DeliveryController::class)]
class DeliveryApiEndpointTest extends ApiTestCase
{

    /**
     * @param array<string, mixed> $params
     */
    public function deliveryApi(string|Blog $blog, array $params = []): Response
    {
        $subdomain = $blog instanceof Blog ? $blog->getSubdomain() : $blog;
        $url = '/api/delivery/v0/' . $subdomain;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();
        if ($response->getStatusCode() === 500) {
            throw new \Exception('API 500: ' . $response->getContent());
        }
        return $response;
    }


    public function test_requires_a_valid_api_key(): void
    {
        $blog = BlogFactory::createOne();

        $this->deliveryApi($blog, ['path' => '']);
        $this->assertResponseFailed(400, 'API Key not set');

        $this->deliveryApi($blog, ['api_key' => 'invalid', 'path' => '']);
        $this->assertResponseFailed(400, 'API Key invalid');
    }

    public function test_returns_bad_request_for_unknown_blog(): void
    {
        $this->deliveryApi('unknown-subdomain', ['api_key' => 'invalid', 'path' => '']);
        $this->assertResponseFailed(400, 'Blog not found');
    }

    public function test_calls_the_delivery_api(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'just testing',
        ]);

        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog,
            'type' => ApiKeyType::DELIVERY,
        ]);

        $this->deliveryApi($blog, [
            'api_key' => $apiKey->getApiKey(),
            'path' => '',
        ]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertSame(200, $json['status']);
        $this->assertSame(base64_encode('just testing'), $json['content']);
        $this->assertSame('file', $json['type']);
        $this->assertSame('template', $json['file_type']);
        $this->assertSame('text/html', $json['mime_type']);
        $this->assertTrue($json['cache']);
        $this->assertSame('no-cache, private', $json['cache_control']);
        $this->assertArrayHasKey('at', $json);
    }

    public function test_does_not_cache_preview(): void
    {
        // TODO: PreviewProcessor is not yet implemented (see Service/Delivery/Processor/PreviewProcessor.php)
        $this->markTestIncomplete('PreviewProcessor is not yet implemented');
    }
}
