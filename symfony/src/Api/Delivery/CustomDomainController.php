<?php

namespace App\Api\Delivery;

use App\Entity\Enum\TlsMode;
use App\Service\AppConfig;
use App\Service\Delivery\BlogHomepageRedirector;
use App\Service\Delivery\DeliveryService;
use App\Service\Hosting\CustomDomain\Acme\AcmeClient;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Service\Hosting\CustomDomain\InternalCustomDomainVerificationService;
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
        private CacheInterface $cache,
        private BlogHomepageRedirector $homepageRedirector,
    )
    {
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
        $keyAuth = $this->cache->get(AcmeClient::ACME_CHALLENGE_CACHE_PREFIX . $token, fn(): string => '');
        return new Response($keyAuth, 200, ['Content-Type' => 'text/plain']);
    }

    #[Route(
        '/{path}',
        name: 'custom_domain_delivery',
        requirements: ['path' => '.*'],
        methods: 'GET',
    )]
    public function serveBlog(string $path, Request $request): Response
    {
        $host = $request->getHost();

        // this happens on port 80 only
        if (
            $host === $this->appConfig->getDomainApp() &&
            !$request->isSecure() &&
            $this->appConfig->getTlsMode() !== TlsMode::DISABLED
        ) {
            return new RedirectResponse('https://' . $host . $request->getRequestUri(), 308);
        }

        $blog = $this->customDomainService->getBlogByCustomDomain($host);

        if ($blog === null) {
            return $this->homepageRedirector->redirect('custom_domain', $host, 'not_found');
        }

        $redirect = $this->homepageRedirector->redirectIfUnavailable($blog, 'custom_domain', $host);
        if ($redirect !== null) {
            return $redirect;
        }

        return $this->deliveryService->getSymfonyResponse($blog, $path);
    }


}
