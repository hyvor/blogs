<?php

namespace App\Service\Delivery\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Error\Error;

class DataApiCaller
{
    public function __construct(private HttpKernelInterface $httpKernel) {}

    /**
     * @param array<string, mixed> $query
     */
    public function callApi(string $subdomain, string $endpoint, array $query = []): mixed
    {
        $endpoint = trim($endpoint, '/');

        $request = Request::create("/api/data/v0/$subdomain/$endpoint", 'GET', $query);
        $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);

        $content = (string) $response->getContent();
        $data = json_decode($content, true);

        if ($response->isSuccessful()) {
            return $data;
        }

        $error = is_array($data) && is_string($data['message'] ?? null) ? $data['message'] : 'Something went wrong';
        throw new Error("Error when calling the Data API /$endpoint endpoint: $error");
    }
}
