<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AppConfig
{

    public function __construct(
        #[Autowire('%env(string:default::DELIVERY_URL)%')]
        private string $deliveryUrl,
    )
    {
    }

    public function getDeliveryUrl(): string
    {
        return $this->deliveryUrl;
    }

    public function getDeliveryDomain(): string
    {
        $host = parse_url($this->deliveryUrl, PHP_URL_HOST);
        return strval($host);
    }

}
