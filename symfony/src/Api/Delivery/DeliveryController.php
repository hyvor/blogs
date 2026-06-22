<?php

namespace App\Api\Delivery;

use App\Entity\Enum\ApiKeyType;
use App\Service\ApiKey\ApiKeyService;
use App\Service\Blog\BlogService;
use App\Service\Delivery\DeliveryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class DeliveryController extends AbstractController
{

    public function __construct(
        private BlogService $blogService,
        private ApiKeyService $apiKeyService,
        private DeliveryService $deliveryService,
    )
    {
    }

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
        $response = (array) $this->deliveryService->getResponse($blog, $path);

        if (isset($response['content'])) {
            $response['content'] = base64_encode($response['content']);
        }

        return new JsonResponse($response);
    }


    #[Route('/blog/{subdomain}/{path}', defaults: ['path' => null], requirements: ['path' => '.*'], methods: ['GET'])]
    public function blogOnSubdirectory(string $subdomain, ?string $path): Response
    {
        $blog = $this->blogService->getBlogBySubdomain($subdomain);
        
        if ($blog === null) {
            throw new NotFoundHttpException('Blog not found');
        }

        $path = '/' . ltrim($path ?? '', '/');

        return $this->deliveryService->getSymfonyResponse($blog, $path);
    }

}