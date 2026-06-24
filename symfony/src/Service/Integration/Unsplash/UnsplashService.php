<?php

namespace App\Service\Integration\Unsplash;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UnsplashService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $accessKey,
    ) {}

    /**
     * @return list<array<string, mixed>>
     * @throws UnsplashSearchException
     */
    public function search(string $search, int $page): array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.unsplash.com/search/photos', [
                'headers' => [
                    'Authorization' => 'Client-ID ' . $this->accessKey,
                ],
                'query' => [
                    'query' => $search,
                    'page' => $page,
                    'per_page' => 30,
                ],
            ]);

            $data = $response->toArray();
        } catch (HttpClientExceptionInterface $e) {
            throw new UnsplashSearchException('Unable to search unsplash', previous: $e);
        }

        /** @var list<array<string, mixed>> $results */
        $results = $data['results'] ?? [];

        return $results;
    }
}
