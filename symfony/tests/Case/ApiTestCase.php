<?php

namespace App\Tests\Case;

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
        string $subdomain,
        string $endpoint,
        array $data = [],
        array $server = [],
        ?AuthUser $user = null,
    ): Response {
        AuthFake::enableForSymfony($this->getContainer(), $user);
        $endpoint = ltrim($endpoint, '/');
        $this->client->request(
            $method,
            '/api/console/v0/blog/' . $subdomain . '/' . $endpoint,
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
        ?AuthUser $user = null,
        ?AuthUserOrganization $organization = null,
        bool $setOrgHeader = true,
    ): Response {
        AuthFake::enableForSymfony($this->getContainer(), $user, $organization);

        $endpoint = ltrim($endpoint, '/');

        if ($organization !== null && $setOrgHeader) {
            $server['HTTP_X_ORGANIZATION_ID'] = $organization->id;
        }

        $this->client->request(
            $method,
            '/api/v2/console/' . $endpoint,
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
