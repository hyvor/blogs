<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\ApiKey\CreateApiKeyInput;
use App\Api\Console\Object\ApiKeyObject;
use App\Entity\ApiKey;
use App\Service\ApiKey\ApiKeyService;
use App\Service\Limit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ApiKeyController extends AbstractController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private ApiKeyService $apiKeyService,
    ) {}

    #[Route('/api-keys', methods: ['GET'])]
    #[ScopeRequired(Scope::API_KEYS_READ)]
    public function getApiKeys(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $apiKeys = $this->apiKeyService->getApiKeys($blog);

        return new JsonResponse(array_map(fn($k) => new ApiKeyObject($k), $apiKeys));
    }

    #[Route('/api-key', methods: ['POST'])]
    #[ScopeRequired(Scope::API_KEYS_WRITE)]
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
    #[ScopeRequired(Scope::API_KEYS_WRITE)]
    public function regenerateApiKey(#[MapBlogEntity] ApiKey $apiKey): JsonResponse
    {
        $apiKey = $this->apiKeyService->regenerateApiKey($apiKey);

        return new JsonResponse(new ApiKeyObject($apiKey));
    }

    #[Route('/api-key/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::API_KEYS_WRITE)]
    public function deleteApiKey(#[MapBlogEntity] ApiKey $apiKey): JsonResponse
    {
        $this->apiKeyService->deleteApiKey($apiKey);

        return new JsonResponse();
    }
}
