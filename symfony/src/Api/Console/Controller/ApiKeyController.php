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
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[OA\Get(
        description: 'Returns all API keys of the blog.',
        summary: 'List API keys',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of API keys.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: ApiKeyObject::class))),
    )]
    public function list(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $apiKeys = $this->apiKeyService->getApiKeys($blog);

        return new JsonResponse(array_map(fn($k) => new ApiKeyObject($k), $apiKeys));
    }

    #[Route('/api-key', methods: ['POST'])]
    #[ScopeRequired(Scope::API_KEYS_WRITE)]
    #[OA\Post(
        description: 'Creates a new API key with the given scopes. The key value is only returned in this response.',
        summary: 'Create an API key',
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the created API key.',
        content: new Model(type: ApiKeyObject::class),
    )]
    public function create(
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
    #[OA\Patch(
        description: 'Generates a new key value for an API key. The previous value stops working immediately.',
        summary: 'Regenerate an API key',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the API key with the new key value.',
        content: new Model(type: ApiKeyObject::class),
    )]
    public function regenerate(#[MapBlogEntity] ApiKey $apiKey): JsonResponse
    {
        $apiKey = $this->apiKeyService->regenerateApiKey($apiKey);

        return new JsonResponse(new ApiKeyObject($apiKey));
    }

    #[Route('/api-key/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::API_KEYS_WRITE)]
    #[OA\Delete(
        description: 'Permanently deletes an API key.',
        summary: 'Delete an API key',
    )]
    #[OA\Response(response: 200, description: 'The API key was deleted.')]
    public function delete(#[MapBlogEntity] ApiKey $apiKey): JsonResponse
    {
        $this->apiKeyService->deleteApiKey($apiKey);

        return new JsonResponse();
    }
}
