<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

// checks if the given links are broken or not
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\LinkAnalyzerLink;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class LinkAnalyzerService
{

    const IGNORE_CODE = -2;

    /**
     * @param string[] $urls
     * @return array<string, int>
     */
    public static function getFromDb(
        Blog $blog,
        array $urls,
        int $validForDays = 7
    ) : array
    {

        $validStart = now()->subDays($validForDays);

        $fromDb = LinkAnalyzerLink::where('blog_id', $blog->id)
            ->whereIn('url', $urls)
            ->get();

        $results = [];

        foreach ($fromDb as $link) {
            if ($link->last_checked_at < $validStart && !$link->ignore) {
                continue;
            }

            $results[$link->url] = $link->ignore ?
                self::IGNORE_CODE :
                $link->status_code;
        }

        return $results;
    }


    /**
     * @param Blog $blog
     * @param array<string, integer> $results
     * @return array<string, integer>
     */
    public static function saveToDb(Blog $blog, array $results) : array
    {

        $now = now();

        foreach ($results as $url => $statusCode) {

            $link = LinkAnalyzerLink::updateOrCreate(
                [
                    'blog_id' => $blog->id,
                    'url' => $url,
                ],
                [
                    'last_checked_at' => $now,
                    'status_code' => $statusCode,
                ]
            );

            if ($link->ignore) {
                $results[$url] = self::IGNORE_CODE;
            }

        }

        return $results;

    }

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
                    ->withOptions([
                        'allow_redirects' => false,
                        'on_headers' => function () {
                            // throw an exception to prevent request from downloading the body
                            // we only need the headers
                            throw new Exception();
                        }
                    ])
                    ->timeout(5) // I guess 5 seconds is enough for HEAD
                    ->get($url);
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

    public static function getLink(Blog $blog, string $url) : ?LinkAnalyzerLink
    {
        return LinkAnalyzerLink::where('blog_id', $blog->id)
            ->where('url', $url)
            ->first();
    }

    public static function ignoreLink(LinkAnalyzerLink $link, bool $status) : void
    {
        $link->ignore = $status;
        $link->save();
    }

    /**
     * @param Blog $blog
     * @return array{ok: integer, redirect: integer, broken: integer, ignored: integer}
     */
    public static function getCountsByStatus(Blog $blog) : array
    {

        $counts = LinkAnalyzerLink::where('blog_id', $blog->id)
            ->selectRaw('
                SUM(IF(`ignore` = 0 AND status_code >= 200 AND status_code < 300, 1, 0)) AS ok,
                SUM(IF(`ignore` = 0 AND status_code >= 300 AND status_code < 400, 1, 0)) AS redirect,
                SUM(IF(`ignore` = 0 AND status_code >= 400, 1, 0)) AS broken,
                SUM(IF(`ignore` = 1, 1, 0)) AS ignored
            ')
            ->first();

        if (!$counts) {
            throw new SafetyException();
        }

        /**
         * @var array{ok: integer, redirect: integer, broken: integer, ignored: integer} $counts
         */
        $counts = $counts->toArray();

        return [
            'ok' => (int) $counts['ok'],
            'redirect' => (int) $counts['redirect'],
            'broken' => (int) $counts['broken'],
            'ignored' => (int) $counts['ignored'],
        ];
    }

}