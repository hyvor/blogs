<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleBlogApiAuthorizationListener;
use App\Api\Console\Input\Blog\ApiKey\CreateApiKeyInput;
use App\Api\Console\Object\ApiKeyObject;
use App\Service\ApiKey\ApiKeyService;
use App\Service\Limit;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ApiKeyController
{
    public function __construct(
        private ConsoleBlogApiAuthorizationListener $blogAuthListener,
        private ApiKeyService $apiKeyService,
    ) {}

    #[Route('/api-keys', methods: ['GET'])]
    public function getApiKeys(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $apiKeys = $this->apiKeyService->getApiKeys($blog);

        return new JsonResponse(array_map(fn($k) => new ApiKeyObject($k), $apiKeys));
    }

    #[Route('/api-key', methods: ['POST'])]
    public function createApiKey(
        #[MapRequestPayload] CreateApiKeyInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->apiKeyService->getApiKeysCount($blog) >= Limit::MAX_API_KEYS_PER_BLOG) {
            throw new UnprocessableEntityHttpException(
                'You have reached the maximum number of API keys (' . Limit::MAX_API_KEYS_PER_BLOG . ')'
            );
        }

        $apiKey = $this->apiKeyService->createApiKey($blog, $input->name, $input->type);

        return new JsonResponse(new ApiKeyObject($apiKey), 201);
    }

    #[Route('/api-key/{id}', methods: ['PATCH'])]
    public function regenerateApiKey(int $id): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $apiKey = $this->apiKeyService->getApiKeyByIdAndBlog($id, $blog);
        $apiKey = $this->apiKeyService->regenerateApiKey($apiKey);

        return new JsonResponse(new ApiKeyObject($apiKey));
    }

    #[Route('/api-key/{id}', methods: ['DELETE'])]
    public function deleteApiKey(int $id): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $apiKey = $this->apiKeyService->getApiKeyByIdAndBlog($id, $blog);
        $this->apiKeyService->deleteApiKey($apiKey);

        return new JsonResponse();
    }
}
