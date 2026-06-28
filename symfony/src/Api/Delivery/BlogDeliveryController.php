<?php

namespace App\Api\Delivery;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogDeliveryController
{

    #[Route(
        '/{path}',
        name: 'blog_delivery',
        requirements: ['path' => '.*'],
        condition: "service('delivery_subdomain_condition').check(request)"
    )]
    public function subdomainDelivery(string $path): Response
    {
        //
    }

    #[Route(
        '/{path}',
        name: 'blog_delivery_custom_domain',
        requirements: ['path' => '.*'],
        condition: "service('custom_domain_condition').check(request)"
    )]
    public function customDomainDelivery(string $path): Response
    {
        //
    }
}
