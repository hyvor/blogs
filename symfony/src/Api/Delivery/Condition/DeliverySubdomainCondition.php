<?php

namespace App\Api\Delivery\Condition;

use App\Service\AppConfig;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
use Symfony\Component\HttpFoundation\Request;

#[AsRoutingConditionService(alias: 'delivery_subdomain_condition')]
class DeliverySubdomainCondition
{

    public function __construct(private AppConfig $appConfig)
    {
    }

    public function check(Request $request): bool
    {
        $host = strtolower(trim($request->getHost()));
        $deliveryDomain = $this->appConfig->getDeliveryDomain();

    }
}
