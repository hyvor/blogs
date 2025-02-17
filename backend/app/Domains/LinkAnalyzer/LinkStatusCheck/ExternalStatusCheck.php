<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

use App\Domains\LinkAnalyzer\LinkStatusCheck\Ignore\KnownFirewall;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExternalStatusCheck implements LinkStatusCheckInterface
{

    /**
     * Number of URLs to concurrently check
     *
     * Historical values:
     * 250 - sometimes caused some URLs to block the crawler
     */
    private const CHUNK_SIZE = 100;

    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    public function check(array $urls): array
    {
        $statuses = [];

        foreach (array_chunk($urls, self::CHUNK_SIZE) as $i => $chunk) {
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
                $responses[$url] = $this->client->request(
                    'HEAD',
                    $url,
                    (new HttpOptions)
                        ->setMaxRedirects(0)
                        ->setTimeout(5) // seconds
                        ->setMaxDuration(10) // seconds
                        ->setHeaders([
                            'User-Agent' => 'Hyvor Blogs Link Analyzer',
                        ])
                        ->toArray()
                );
            } // @codeCoverageIgnoreStart
            catch (TransportExceptionInterface $e) {
                // this is thrown when an unsupported option is passed
                // this should not usually happen
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

                // on unauthorized, check for known firewalls
                if ($httpStatusCode === 401 || $httpStatusCode === 403) {
                    $headers = $response->getHeaders(false);

                    $headers = array_map(fn($header) => $header[0], $headers);
                    $headers = array_change_key_case($headers, CASE_LOWER);

                    $firewall = KnownFirewall::isKnownFirewall($headers);
                    if ($firewall) {
                        $statuses[$url] = new StatusResult(
                            StatusCheckType::EXTERNAL,
                            httpStatus: $httpStatusCode,
                            ignored: true,
                            ignoreReason: IgnoreReasonEnum::KNOWN_FIREWALL,
                            comment: 'firewall: ' . $firewall->value
                        );
                        continue;
                    }
                }

                $statuses[$url] = new StatusResult(
                    StatusCheckType::EXTERNAL,
                    httpStatus: $httpStatusCode,
                );
            } catch (TransportExceptionInterface $e) {
                $statuses[$url] = new StatusResult(
                    StatusCheckType::EXTERNAL,
                    httpStatus: 0,
                    comment: 'connection error'
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