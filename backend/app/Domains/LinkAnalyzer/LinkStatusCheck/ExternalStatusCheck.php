<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

use Illuminate\Support\Facades\Log;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExternalStatusCheck implements LinkStatusCheckInterface
{

    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    public function check(array $urls): array
    {
        $statuses = [];

        foreach (array_chunk($urls, 250) as $i => $chunk) {
            $chunkStatuses = $this->checkChunk($chunk, $i);
            $statuses = array_merge($statuses, $chunkStatuses);
        }

        return $statuses;
    }

    /**
     * @param string[] $urls
     * @return array<string, StatusResult>
     */
    public function checkChunk(array $urls, int $index): array
    {
        $responses = [];
        $statuses = [];

        $startTime = microtime(true);

        foreach ($urls as $url) {
            try {
                $responses[$url] = $this->client->request('HEAD', $url, [
                    'max_redirects' => 0,
                    'timeout' => 2.5, // seconds
                    'max_duration' => 5, // seconds
                ]);
            } // @codeCoverageIgnoreStart
            catch (TransportExceptionInterface $e) {
                // this is thrown when an unsupported option is passed
                Log::critical('Unsupported option passed to the HTTP client in external status check', [
                    'url' => $url,
                    'exception' => $e,
                ]);
                $statuses[$url] = new StatusResult(
                    StatusCheckType::EXTERNAL,
                    httpStatus: 500,
                    comment: 'Unsupported option passed to the HTTP client'
                );
                // @codeCoverageIgnoreEnd
            }
        }


        foreach ($responses as $url => $response) {
            try {
                $httpStatusCode = $response->getStatusCode();

                $statuses[$url] = new StatusResult(
                    StatusCheckType::EXTERNAL,
                    httpStatus: $httpStatusCode,
                );
            } catch (TransportExceptionInterface $e) {
                $statuses[$url] = new StatusResult(
                    StatusCheckType::EXTERNAL,
                    httpStatus: 500,
                    comment: 'network error'
                );
            }
        }

        $endTime = microtime(true);
        event(
            new ExternalStatusCheckChunkDoneEvent(
                count($urls),
                $index,
                $endTime - $startTime,
            )
        );

        return $statuses;
    }

}