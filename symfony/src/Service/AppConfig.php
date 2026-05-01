<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AppConfig
{

    public function __construct(
        #[Autowire('%env(string:DOMAIN_APP)%')]
        private string $domainApp,
        #[Autowire('%env(default::string:DELIVERY_URL)%')]
        private string $deliveryUrl = '',
        #[Autowire('%env(default::string:HYVOR_INSTANCE)%')]
        private string $hyvorInstance = '',
    ) {}

    public function getDeliveryUrl(): string
    {
        return $this->deliveryUrl;
    }

    public function getDeliveryDomain(): string
    {
        $host = parse_url($this->deliveryUrl, PHP_URL_HOST);
        return strval($host);
    }

    public function getDomainApp(): string
    {
        return $this->domainApp;
    }

    public function getHyvorInstance(): string
    {
        return $this->hyvorInstance;
    }
}
