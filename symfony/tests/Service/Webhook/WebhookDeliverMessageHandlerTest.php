<?php

namespace App\Tests\Service\Webhook;

use App\Entity\Enum\WebhookDeliveryStatus;
use App\Entity\Enum\WebhookEvent;
use App\Message\WebhookDeliverMessage;
use App\MessageHandler\WebhookDeliverMessageHandler;
use App\Service\Webhook\WebhookDeliveryService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookDeliveryFactory;
use App\Tests\Factory\WebhookFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(WebhookDeliverMessageHandler::class)]
#[UsesClass(WebhookDeliveryService::class)]
#[UsesClass(WebhookDeliverMessage::class)]
class WebhookDeliverMessageHandlerTest extends KernelTestCase
{
    private function handler(HttpClientInterface $httpClient): WebhookDeliverMessageHandler
    {
        $service = new WebhookDeliveryService($this->getService(\Doctrine\ORM\EntityManagerInterface::class), $httpClient);
        return new WebhookDeliverMessageHandler($this->getService(\Doctrine\ORM\EntityManagerInterface::class), $service);
    }

    private function makeDelivery(string $url = 'https://example.com/hook'): \App\Entity\WebhookDelivery
    {
        $blog = BlogFactory::createOne();
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => $url,
            'events' => [WebhookEvent::CACHE_ALL],
            'secret' => 'test-secret',
        ]);
        return WebhookDeliveryFactory::createOne([
            'webhook' => $webhook,
            'webhook_id' => $webhook->getId(),
            'url' => $url,
            'event' => WebhookEvent::CACHE_ALL,
            'data' => [],
            'status' => WebhookDeliveryStatus::PENDING,
            'created_at' => new \DateTimeImmutable(),
        ]);
    }

    public function test_throws_unrecoverable_when_delivery_not_found(): void
    {
        $handler = $this->handler(new MockHttpClient());

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $handler(new WebhookDeliverMessage(99999));
    }

    public function test_successful_delivery_sets_status_to_success(): void
    {
        $delivery = $this->makeDelivery();

        $response = new MockResponse('OK', ['http_code' => 200]);
        $http = new MockHttpClient($response);
        $handler = $this->handler($http);

        $handler(new WebhookDeliverMessage($delivery->getId()));

        $this->getService(\Doctrine\ORM\EntityManagerInterface::class)->refresh($delivery);
        $this->assertSame(WebhookDeliveryStatus::SUCCESS, $delivery->getStatus());
        $this->assertSame(200, $delivery->getHttpStatus());
        $this->assertSame(0, $delivery->getTryCount());

        $this->assertSame('POST', $response->getRequestMethod());
        $this->assertSame($delivery->getUrl(), $response->getRequestUrl());

        $options = $response->getRequestOptions();

        $body = $options['body'];
        $this->assertIsString($body);

        $headers = $options['normalized_headers'];
        $this->assertArrayHasKey('x-signature', $headers);
        $signature = $headers['x-signature'][0];

        $expectedSignature = hash_hmac('sha256', $body, 'test-secret');
        $this->assertSame('X-Signature: ' . $expectedSignature, $signature);
    }

    public function test_failed_delivery_increments_try_count_and_sets_retrying(): void
    {
        $delivery = $this->makeDelivery();
        $http = new MockHttpClient(new MockResponse('Bad Gateway', ['http_code' => 502]));
        $handler = $this->handler($http);

        try {
            $handler(new WebhookDeliverMessage($delivery->getId()));
            $this->fail('Expected RecoverableMessageHandlingException');
        } catch (RecoverableMessageHandlingException) {
            $this->getService(\Doctrine\ORM\EntityManagerInterface::class)->refresh($delivery);
            $this->assertSame(WebhookDeliveryStatus::RETRYING, $delivery->getStatus());
            $this->assertSame(1, $delivery->getTryCount());
            $this->assertNotNull($delivery->getLastTryAt());
        }
    }

    public function test_delivery_sets_failed_after_max_retries(): void
    {
        $delivery = $this->makeDelivery();
        // Simulate already at MAX_RETRIES - 1 so next failure exhausts retries
        $delivery->setTryCount(2);
        $this->getService(\Doctrine\ORM\EntityManagerInterface::class)->flush();

        $http = new MockHttpClient(new MockResponse('Error', ['http_code' => 500]));
        $handler = $this->handler($http);

        try {
            $handler(new WebhookDeliverMessage($delivery->getId()));
            $this->fail('Expected UnrecoverableMessageHandlingException');
        } catch (UnrecoverableMessageHandlingException) {
            $this->getService(\Doctrine\ORM\EntityManagerInterface::class)->refresh($delivery);
            $this->assertSame(WebhookDeliveryStatus::FAILED, $delivery->getStatus());
            $this->assertSame(3, $delivery->getTryCount());
        }
    }

    public function test_retry_delay_uses_try_count_from_retries_array(): void
    {
        $delivery = $this->makeDelivery();
        $http = new MockHttpClient(new MockResponse('error', ['http_code' => 500]));
        $handler = $this->handler($http);

        try {
            $handler(new WebhookDeliverMessage($delivery->getId()));
        } catch (RecoverableMessageHandlingException $e) {
            // try_count is 1 after first failure -> index = try_count-1 = 0 → RETRIES[0] = 60s
            $this->assertSame(WebhookDeliverMessageHandler::RETRIES[0] * 1000, $e->getRetryDelay());
            return;
        }

        $this->fail('Expected RecoverableMessageHandlingException');
    }
}
