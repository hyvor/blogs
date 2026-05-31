<?php

namespace App\Tests\Case;

use App\Entity\Blog;
use App\Entity\User as BlogUser;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Symfony\Component\HttpFoundation\Response;

class ApiTestCase extends \Hyvor\Internal\Bundle\Testing\ApiTestCase
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $server
     */
    public function consoleBlogApi(
        string $method,
        string|Blog $subdomain,
        string $endpoint,
        array $data = [],
        array $server = [],
        BlogUser|AuthUser|int|null $user = null,
    ): Response {
        $authUser = null;
        if ($user instanceof BlogUser) {
            $authUser = AuthFake::generateUser(['id' => $user->getHyvorUserId()]);
        } elseif ($user instanceof AuthUser) {
            $authUser = $user;
        } elseif (is_int($user)) {
            $authUser = AuthFake::generateUser(['id' => $user]);
        }
        AuthFake::enableForSymfony($this->getContainer(), $authUser);
        $endpoint = ltrim($endpoint, '/');
        $this->client->request(
            $method,
            '/api/console/v0/blog/' . ($subdomain instanceof Blog ? $subdomain->getSubdomain() : $subdomain) . '/' . $endpoint,
            server: array_merge(['CONTENT_TYPE' => 'application/json'], $server),
            content: (string)json_encode($data),
        );
        $response = $this->client->getResponse();
        if ($response->getStatusCode() === 500) {
            throw new \Exception('API 500: ' . $response->getContent());
        }
        return $response;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $server
     */
    public function consoleOrgApi(
        string $method,
        string $endpoint,
        array $data = [],
        array $server = [],
        AuthUser|int|null $user = null,
        ?AuthUserOrganization $organization = null,
        bool $setOrgHeader = true,
    ): Response {
        $authUser = null;
        if ($user instanceof AuthUser) {
            $authUser = $user;
        } elseif (is_int($user)) {
            $authUser = AuthFake::generateUser(['id' => $user]);
        }
        AuthFake::enableForSymfony($this->getContainer(), $authUser, $organization);

        $endpoint = ltrim($endpoint, '/');

        if ($organization !== null && $setOrgHeader) {
            $server['HTTP_X_ORGANIZATION_ID'] = $organization->id;
        }

        $this->client->request(
            $method,
            '/api/console/v0/' . $endpoint,
            server: array_merge(['CONTENT_TYPE' => 'application/json'], $server),
            content: (string)json_encode($data),
        );

        $response = $this->client->getResponse();
        if ($response->getStatusCode() === 500) {
            throw new \Exception('API 500: ' . $response->getContent());
        }
        return $response;
    }
}
