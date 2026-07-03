<?php

namespace App\Api\Delivery;

use App\Service\AppConfig;
use App\Service\CustomDomain\Acme\AcmeClient;
use App\Service\CustomDomain\CustomDomainService;
use App\Service\CustomDomain\InternalCustomDomainVerificationService;
use App\Service\Delivery\DeliveryService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

class CustomDomainController
{

    public function __construct(
        private AppConfig $appConfig,
        private CustomDomainService $customDomainService,
        private DeliveryService $deliveryService,
        private InternalCustomDomainVerificationService $internalCustomDomainVerificationService,
        private CacheInterface $cache
    )
    {
    }

    #[Route(
        '/{path}',
        name: 'custom_domain_delivery',
        requirements: ['path' => '.*'],
        methods: 'GET',
        schemes: 'https'
    )]
    public function serveBlog(string $path, Request $request): Response
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

    #[Route(
        '/.well-known/hyvor-blogs-verification.txt',
        methods: 'GET',
        schemes: 'http'
    )]
    public function serveInternalVerificationFile(Request $request): Response
    {
        $host = $request->getHost();
        $token = $this->internalCustomDomainVerificationService->getVerificationToken($host);
        return new Response($token ?? '', 200, ['Content-Type' => 'text/plain']);
    }

    #[Route(
        '/.well-known/acme-challenge/{token}',
        methods: 'GET',
        schemes: 'http'
    )]
    public function serveAcmeVerification(string $token): Response
    {
        $keyAuth = $this->cache->get(AcmeClient::ACME_CHALLENGE_CACHE_PREFIX . $token, fn() => null);
        return new Response($keyAuth ?? '', 200, ['Content-Type' => 'text/plain']);
    }


}
