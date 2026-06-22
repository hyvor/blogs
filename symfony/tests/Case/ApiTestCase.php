<?php

namespace App\Tests\Case;

use App\Entity\Blog;
use App\Entity\User as BlogUser;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Bundle\Entity\SudoUser;
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
            $authUser = AuthFake::generateUser(['id' => (int) $user->getHyvorUserId()]);
        } elseif ($user instanceof AuthUser) {
            $authUser = $user;
        } elseif (is_int($user)) {
            $authUser = AuthFake::generateUser(['id' => $user]);
        }

        $blog = $subdomain instanceof Blog ? $subdomain : $this->getEm()->getRepository(Blog::class)->findOneBy(['subdomain' => $subdomain]);
        $orgId = $blog?->getOrganizationId() ?? 0;

        AuthFake::enableForSymfony(
            $this->getContainer(),
            $authUser,
            new AuthUserOrganization($orgId, '', 'admin')
        );

        $endpoint = ltrim($endpoint, '/');
        $this->client->request(
            $method,
            '/api/console/v0/blog/' . ($subdomain instanceof Blog ? $subdomain->getSubdomain() : $subdomain) . '/' . $endpoint,
            server: array_merge([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_ORGANIZATION_ID' => $orgId,
            ], $server),
            content: (string) json_encode($data),
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
            content: (string) json_encode($data),
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
    public function sudoApi(
        string $method,
        string $endpoint,
        array $data = [],
        array $server = [],
        AuthUser|int|null $user = null,
        ?string $sudoRole = 'sudo',
    ): Response {
        $authUser = null;
        if ($user instanceof AuthUser) {
            $authUser = $user;
        } elseif (is_int($user)) {
            $authUser = AuthFake::generateUser(['id' => $user]);
        }

        AuthFake::enableForSymfony($this->getContainer(), $authUser);

        if ($authUser !== null && $sudoRole !== null) {
            $sudoUser = new SudoUser();
            $sudoUser->setUserId($authUser->id);
            $sudoUser->setRole($sudoRole);
            $sudoUser->setCreatedAt(new \DateTimeImmutable());
            $sudoUser->setUpdatedAt(new \DateTimeImmutable());
            $this->getEm()->persist($sudoUser);
            $this->getEm()->flush();
        }

        $endpoint = ltrim($endpoint, '/');
        $this->client->request(
            $method,
            '/api/sudo/' . $endpoint,
            server: array_merge(['CONTENT_TYPE' => 'application/json'], $server),
            content: (string) json_encode($data),
        );

        $response = $this->client->getResponse();
        if ($response->getStatusCode() === 500) {
            throw new \Exception('API 500: ' . $response->getContent());
        }
        return $response;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function dataApi(string|Blog $blog, string $endpoint, array $params = []): Response
    {
        $subdomain = $blog instanceof Blog ? $blog->getSubdomain() : $blog;
        $endpoint = ltrim($endpoint, '/');
        $url = '/api/data/v0/' . $subdomain . '/' . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();
        if ($response->getStatusCode() === 500) {
            throw new \Exception('API 500: ' . $response->getContent());
        }
        return $response;
    }
}
