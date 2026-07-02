<?php

namespace App\Api\Delivery;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CustomDomainController
{

    #[Route(
        '/{path}',
        name: 'custom_domain_delivery',
        requirements: ['path' => '.*'],
    )]
    public function handle(string $path, Request $request): Response
    {
        $host = $request->getHost();
        // TODO: after TLS is fully done.
    }

}
