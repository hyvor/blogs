<?php

namespace App\Service\Integration\HyvorPost;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\Component\Component;
use Hyvor\Sdk\Post\PostClient;
use Hyvor\Internal\CloudApi\CloudApiService;

class HyvorPostService
{

    private const array REQUIRED_SCOPES = [
        // to create a new newsletter when connecting
        PostScope::ORG_NEWSLETTERS_CREATE,

        // to list the neswletter to choose from when connecting (currently not used)
        PostScope::ORG_NEWSLETTERS_READ,

        // add, remove users automatically as they are added/removed in the blog
        PostScope::USERS_READ,
        PostScope::USERS_WRITE,
    ];

    public function __construct(
        private CloudApiService $cloudApiService,
    ) {}

    private function getClient(int $orgId): PostClient
    {
        $hyvorClient = $this->cloudApiService->getHyvorClientForOrganization(
            $orgId,
            Component::POST,
            self::REQUIRED_SCOPES
        );

        return $hyvorClient->post;
    }

    public function createNewsletter(
        int $orgId,
        string $name,
        string $subdomain
    ): void
    {
        $this->getClient($orgId)->newsletters->create($name, $subdomain);
    }

}
