<?php

namespace App\Api\Delivery;

use App\Service\AppConfig;
use App\Service\CustomDomain\CustomDomainService;
use App\Service\Delivery\DeliveryService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CustomDomainController
{

    public function __construct(
        private AppConfig $appConfig,
        private CustomDomainService $customDomainService,
        private DeliveryService $deliveryService,
    )
    {
    }

    #[Route(
        '/{path}',
        name: 'custom_domain_delivery',
        requirements: ['path' => '.*'],
    )]
    public function handle(string $path, Request $request): Response
    {
        $host = $request->getHost();
        $blog = $this->customDomainService->getBlogByCustomDomain($host);

        if ($blog === null) {
            return new RedirectResponse(
                'https://' . $this->appConfig->getDomainApp() . '/?via=custom_domain&host=' . $host,
                302
            );
        }

        return $this->deliveryService->getSymfonyResponse($blog, $path);
    }

}
