<?php

namespace App\Tests\Case;

class ApiTestCase extends \Hyvor\Internal\Bundle\Testing\ApiTestCase
{

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
    public function consoleApi(
        string $method,
        string $endpoint,
        array $data = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?UserSession $session = null,
        bool $expect5xx = false,
        bool $setOrgHeader = true,
    ): Response {
        $endpoint = ltrim($endpoint, '/');
        $url = '/api/v2/console/' . $endpoint;

        if ($session) {
            $cookies[SessionService::SESSION_COOKIE_NAME] = $session->getKey();
            // set csrf (#484)
            // $server['HTTP_X_CSRF_TOKEN'] = $session->csrf_token;

            if ($setOrgHeader) {
                $server['HTTP_X_ORGANIZATION_ID'] = $session->getUser()->getCurrentOrganization()?->getId();
            }
        }

        foreach ($cookies as $name => $value) {
            $this->client->getCookieJar()->set(new Cookie($name, $value));
        }

        $this->client->request(
            $method,
            $url,
            files: $files,
            server: array_merge([
                'CONTENT_TYPE' => 'application/json',
            ], $server),
            content: (string)json_encode($data),
        );

        $response = $this->client->getResponse();

        if ($response->getStatusCode() === 500 && !$expect5xx) {
            throw new \Exception(
                'API call failed with status code 500. ' .
                'Response: ' . $response->getContent(),
            );
        }

        return $response;
    }


}
