<?php

namespace App\Service\LinkAnalysis\StatusCheck;

use App\Entity\Enum\LinkAnalyzerCheckType;
use App\Service\App\HttpBot;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExternalLinkStatusCheck implements LinkStatusCheckInterface
{
    /**
     * Number of URLs to concurrently check
     *
     * Historical values:
     * 250 - sometimes caused some URLs to block the crawler
     */
    private const int CHUNK_SIZE = 100;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {}

    /**
     * @param string[] $urls
     * @return array<string, StatusResult>
     */
    public function check(array $urls): array
    {
        $statuses = [];

        foreach (array_chunk($urls, self::CHUNK_SIZE) as $chunk) {
            $statuses = array_merge($statuses, $this->checkChunk($chunk));
        }

        return $statuses;
    }

    /**
     * @param string[] $urls
     * @return array<string, StatusResult>
     */
    private function checkChunk(array $urls): array
    {
        $responses = [];
        $statuses = [];

        foreach ($urls as $url) {
            try {
                $responses[$url] = $this->httpClient->request('HEAD', $url, [
                    'max_redirects' => 0,
                    'timeout' => 5,
                    'max_duration' => 10,
                    'headers' => ['User-Agent' => HttpBot::USER_AGENT],
                ]);
            } // @codeCoverageIgnoreStart
            catch (TransportExceptionInterface $e) {
                // this is thrown when an unsupported option is passed
                // this should not usually happen
                $this->logger->critical('Unsupported option passed to the HTTP client in external status check', [
                    'url' => $url,
                    'exception' => $e->getMessage(),
                ]);
                $statuses[$url] = new StatusResult(
                    LinkAnalyzerCheckType::EXTERNAL,
                    500,
                    ignored: true,
                    ignoreReason: IgnoreReason::INTERNAL_ERROR,
                    comment: 'Unsupported option passed to the HTTP client',
                );
            } // @codeCoverageIgnoreEnd
        }

        foreach ($responses as $url => $response) {
            try {
                $httpStatusCode = $response->getStatusCode();

                if ($httpStatusCode === 401 || $httpStatusCode === 403) {
                    $headers = $response->getHeaders(false);
                    $headers = array_map(fn($h) => $h[0], $headers);
                    $headers = array_change_key_case($headers, CASE_LOWER);

                    $firewall = KnownFirewall::detect($headers);
                    if ($firewall !== null) {
                        $statuses[$url] = new StatusResult(
                            LinkAnalyzerCheckType::EXTERNAL,
                            $httpStatusCode,
                            ignored: true,
                            ignoreReason: IgnoreReason::KNOWN_FIREWALL,
                            comment: 'firewall: ' . $firewall->value,
                        );
                        continue;
                    }
                }

                $statuses[$url] = new StatusResult(LinkAnalyzerCheckType::EXTERNAL, $httpStatusCode);
            } catch (TransportExceptionInterface) {
                $statuses[$url] = new StatusResult(
                    LinkAnalyzerCheckType::EXTERNAL,
                    0,
                    comment: 'connection error',
                );
            }
        }

        return $statuses;
    }
}
