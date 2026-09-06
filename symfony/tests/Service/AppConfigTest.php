<?php

namespace App\Tests\Service;

use App\Entity\Enum\TlsMode;
use App\Service\AppConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AppConfig::class)]
class AppConfigTest extends TestCase
{

    public function test_getters_return_constructed_values(): void
    {
        $config = new AppConfig(
            version: '1.2.3',
            domainApp: 'blogs.hyvor.com',
            deliveryUrl: 'https://hyvorblogs.io',
            tlsMode: 'auto',
            unsplashAccessKey: 'unsplash-key',
            openAiApiKey: 'openai-key',
            anthropicApiKey: 'anthropic-key',
            mistralApiKey: 'mistral-key',
        );

        $this->assertSame('blogs.hyvor.com', $config->getDomainApp());
        $this->assertSame('https://hyvorblogs.io', $config->getDeliveryUrl());
        $this->assertSame('hyvorblogs.io', $config->getDeliveryDomain());
        $this->assertSame(TlsMode::AUTO, $config->getTlsMode());
        $this->assertSame('unsplash-key', $config->getUnsplashAccessKey());
        $this->assertSame('openai-key', $config->getOpenAiApiKey());
        $this->assertSame('anthropic-key', $config->getAnthropicApiKey());
        $this->assertSame('mistral-key', $config->getMistralApiKey());
        $this->assertSame('HyvorBlogsBot/1.2.3 (+https://blogs.hyvor.com)', $config->getHttpBotUserAgent());
    }

    public function test_delivery_domain_is_null_when_delivery_url_is_null(): void
    {
        $config = new AppConfig(
            version: '1.0.0',
            domainApp: 'blogs.hyvor.com',
            deliveryUrl: null,
        );

        $this->assertNull($config->getDeliveryUrl());
        $this->assertNull($config->getDeliveryDomain());
    }

}
