<?php

namespace App\Service;

use App\Entity\Enum\TlsMode;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AppConfig
{

    public function __construct(
        #[Autowire('%env(string:APP_VERSION)%')]
        private string $version,

        #[Autowire('%env(string:DOMAIN_APP)%')]
        private string $domainApp,
        #[Autowire('%env(default::DELIVERY_URL)%')]
        private ?string $deliveryUrl = null,
        #[Autowire('%env(default::MEDIA_URL)%')]
        private ?string $mediaUrl = null,

        #[Autowire('%env(string:TLS_MODE)%')]
        private string $tlsMode = 'auto',

        #[Autowire('%env(string:default::UNSPLASH_ACCESS_KEY)%')]
        #[\SensitiveParameter]
        private string $unsplashAccessKey = '',

        // AI keys
        #[Autowire('%env(string:default::OPENAI_API_KEY)%')]
        #[\SensitiveParameter]
        private string $openAiApiKey = '',

        #[Autowire('%env(string:default::ANTHROPIC_API_KEY)%')]
        #[\SensitiveParameter]
        private string $anthropicApiKey = '',

        #[Autowire('%env(string:default::MISTRAL_API_KEY)%')]
        #[\SensitiveParameter]
        private string $mistralApiKey = '',
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDomainApp(): string
    {
        return $this->domainApp;
    }

    public function getDeliveryUrl(): ?string
    {
        return $this->deliveryUrl;
    }

    public function getDeliveryDomain(): ?string
    {
        if ($this->deliveryUrl === null) {
            return null;
        }
        $host = parse_url($this->deliveryUrl, PHP_URL_HOST);
        return $host !== false ? strval($host) : null;
    }

    /**
     * Dedicated URL to serve media from (ex: https://media.yourcompany.com), without a trailing slash.
     * If null, media is served from DOMAIN_APP/media
     */
    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl !== null ? rtrim($this->mediaUrl, '/') : null;
    }

    public function getMediaDomain(): ?string
    {
        if ($this->mediaUrl === null) {
            return null;
        }
        $host = parse_url($this->mediaUrl, PHP_URL_HOST);
        return is_string($host) ? $host : null;
    }

    public function isMediaUrlHttps(): bool
    {
        return $this->mediaUrl !== null && str_starts_with($this->mediaUrl, 'https://');
    }

    public function getTlsMode(): TlsMode
    {
        return TlsMode::from($this->tlsMode);
    }

    public function getUnsplashAccessKey(): ?string
    {
        return $this->unsplashAccessKey;
    }

    public function getOpenAiApiKey(): string
    {
        return $this->openAiApiKey;
    }

    public function getAnthropicApiKey(): string
    {
        return $this->anthropicApiKey;
    }

    public function getMistralApiKey(): string
    {
        return $this->mistralApiKey;
    }

    public function getHttpBotUserAgent(): string
    {
        $url = 'https://' . $this->getDomainApp();
        return "HyvorBlogsBot/$this->version (+$url)";
    }

}
