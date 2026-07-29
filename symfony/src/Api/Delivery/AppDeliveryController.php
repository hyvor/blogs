<?php

namespace App\Api\Delivery;

use App\Entity\Enum\ApiKeyType;
use App\Entity\Enum\BlogHostingAt;
use App\Service\ApiKey\ApiKeyService;
use App\Service\AppConfig;
use App\Service\Blog\BlogService;
use App\Service\Delivery\DeliveryService;
use App\Service\Route\PermalinkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AppDeliveryController extends AbstractController
{

    public function __construct(
        private BlogService $blogService,
        private ApiKeyService $apiKeyService,
        private DeliveryService $deliveryService,
        private AppConfig $appConfig,
        private PermalinkService $permalinkService
    ) {}

    #[Route('/api/delivery/v0/{subdomain}', methods: ['GET'])]
    public function api(string $subdomain, Request $request): JsonResponse
    {
        $blog = $this->blogService->getBlogBySubdomain($subdomain);

        if (!$blog) {
            throw new BadRequestHttpException('Blog not found');
        }

        $apiKeyString = strval($request->query->get('api_key', ''));

        if (!$apiKeyString) {
            throw new BadRequestHttpException('API Key not set');
        }

        if (!$this->apiKeyService->getByRawKey($blog, $apiKeyString, ApiKeyType::DELIVERY)) {
            throw new BadRequestHttpException('API Key invalid');
        }

        $path = strval($request->query->get('path', ''));
        $response = $this->deliveryService->getResponse($blog, $path);

        return new JsonResponse($response);
    }

    #[Route('/blog/{subdomain}/{path}', requirements: ['path' => '.*'], defaults: ['path' => null], methods: ['GET'])]
    public function blogOnSubdirectory(string $subdomain, ?string $path): Response
    {
        $blog = $this->blogService->getBlogBySubdomain($subdomain);

        if ($blog === null) {
            return new Response('Blog not found', 404);
        }

        $path = '/' . ltrim($path ?? '', '/');

        if (
            $this->appConfig->getDeliveryUrl() !== null ||
            $blog->getHostingAt() !== BlogHostingAt::SUBDOMAIN
        ) {
            return new RedirectResponse($this->permalinkService->getBlogUrlWithPath($blog, $path), 302);
        }

        return $this->deliveryService->getSymfonyResponse($blog, $path);
    }
}
