<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AppConfig
{

    public function __construct(
        #[Autowire('%env(string:DOMAIN_APP)%')]
        private string $domainApp,
        #[Autowire('%env(default::DELIVERY_URL)%')]
        private ?string $deliveryUrl = null,

        #[Autowire('%env(string:default::UNSPLASH_ACCESS_KEY)%')]
        private ?string $unsplashAccessKey = null,
    ) {
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

    public function getUnsplashAccessKey(): ?string
    {
        return $this->unsplashAccessKey;
    }

}
