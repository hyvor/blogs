<?php

namespace App\Service\UrlData;

use Hyvor\Internal\Component\Component;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\InternalApi\InternalApi;

class UrlDataFetchService
{
    public function __construct(private InternalApi $internalApi) {}

    /**
     * @return array<mixed>
     */
    public function fetch(string $url, string $type): array
    {
        try {
            return $this->internalApi->call(Component::CORE, '/unfold/unfold', [
                'url' => $url,
                'type' => $type,
            ]);
        } catch (InternalApiCallFailedException $e) {
            throw new UrlDataFetchException('Unable to fetch data', previous: $e);
        }
    }
}
