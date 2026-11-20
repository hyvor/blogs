<?php

namespace App\Api\Delivery;

use App\Service\AppConfig;
use App\Service\Blog\BlogService;
use App\Service\Delivery\BlogHomepageRedirector;
use App\Service\Delivery\DeliveryService;
use App\Service\Route\PermalinkService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class SubdomainController
{
    public function __construct(
        private DeliveryService $deliveryService,
        private AppConfig $appConfig,
        private BlogService $blogService,
        private PermalinkService $permalinkService,
        private BlogHomepageRedirector $homepageRedirector,
    ) {}

    #[Route(
        '/{path}',
        name: 'subdomain_delivery',
        requirements: ['path' => '.*'],
    )]
    public function handle(string $path, Request $request): Response
    {
        $deliveryDomain = $this->appConfig->getDeliveryDomain();
        if ($deliveryDomain === null) {
            throw new HttpException(500, 'Delivery domain is not configured.');
        }

        $host = $request->getHost();
        $subdomain = $this->getSubdomain($host, $deliveryDomain);

        if ($subdomain === null) {
            throw new HttpException(
                500,
                'Unable to determine subdomain from host: ' . $host . ' and delivery domain: ' . $deliveryDomain
            );
        }

        $blog = $this->blogService->getBlogBySubdomain($subdomain);

        if ($blog === null) {
            return $this->homepageRedirector->redirect('subdomain', $host, 'not_found');
        }

        $redirect = $this->homepageRedirector->redirectIfUnavailable($blog, 'subdomain', $host);
        if ($redirect !== null) {
            return $redirect;
        }

        if (
            ($blog->getCustomDomain() !== null || $blog->getHostingUrl() !== null) &&
            $blog->getHostingRedirectSubdomain() &&
            // legacy media URLs ({blogUrl}/media/...) are always served, as the final host (ex: headless) may not serve them
            !str_starts_with($path, 'media/')
        ) {
            return new RedirectResponse($this->permalinkService->getBlogUrlWithPath($blog, $path), 302);
        }

        return $this->deliveryService->getSymfonyResponse($blog, $path);
    }

    private function getSubdomain(string $hostHeader, string $deliveryDomain): ?string
    {
        $host = strtolower(trim($hostHeader));
        $deliveryDomain = strtolower(trim($deliveryDomain));


        if (str_ends_with($host, '.' . $deliveryDomain)) {
            return substr($host, 0, -strlen('.' . $deliveryDomain));
        }

        return null;
    }

}
