<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

// checks if the given links are broken or not
use App\Exceptions\SafetyException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class LinkAnalyzerService
{

    /**
     * @param string[] $urls
     * @return array<string, int>
     */
    public static function analyze(array $urls)
    {

        if (count($urls) > 100) {
            throw new SafetyException('Too many urls to analyze');
        }

        $results = [];

        /**
         * Important note:
         * on errors, pool() method sets the response to that error (weird)
         * that's why we need to check if the response is an instance of Response
         * if it's not, then it's an error
         *
         * tested with:
         *  \GuzzleHttp\Exception\ConnectException (DNS error)
         *  \GuzzleHttp\Exception\ConnectException (connection error)
         *  \GuzzleHttp\Exception\ConnectException (timeout error)
         *  \GuzzleHttp\Exception\RequestException (SSL error)
         */

        /** @var array<string, mixed> $responses */
        $responses = Http::pool(function (Pool $pool) use ($urls) {
            foreach ($urls as $url) {
                $pool
                    ->as($url)
                    ->timeout(5) // I guess 5 seconds is enough for HEAD
                    ->head($url);
            }
        });

        foreach ($urls as $url) {
            $response = $responses[$url] ?? null;
            if (!$response) {
                throw new SafetyException('Response not found for url: ' . $url);
            }

            if ($response instanceof Response) {
                $status = $response->status();
            } else {
                $status = 500;
            }

            $results[$url] = $status;
        }

        return $results;

    }

}