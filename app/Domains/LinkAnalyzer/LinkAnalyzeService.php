<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

// checks if the given links are broken or not
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\LinkAnalyzerLink;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class LinkAnalyzeService
{

    const IGNORE_CODE = -2;


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


    /**
     * @param Collection<int, LinkAnalyzerLink> $links
     * @return array<string, int>
     */
    public static function getResultsFromLinks(Collection $links) : array
    {
        $results = [];

        foreach ($links as $link) {
            $results[$link->url] = $link->ignore ? self::IGNORE_CODE : $link->status_code;
        }

        return $results;
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
                SUM(IF(`ignore` = 0 AND (status_code >= 400 OR status_code < 200), 1, 0)) AS broken,
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

    /**
     * @return Collection<int, LinkAnalyzerLink>
     */
    public static function getLinksOfBlog(
        Blog $blog,
        ?LinkStatusTypeEnum $type = null,
        int $limit,
        int $offset
    ) : Collection
    {

        return LinkAnalyzerLink::where('blog_id', $blog->id)
            ->with('postVariant')
            ->selectRaw('
                *,
                IF(`ignore` = 0 AND status_code >= 200 AND status_code < 300, 1, 0) AS ok,
                IF(`ignore` = 0 AND status_code >= 300 AND status_code < 400, 1, 0) AS redirect,
                IF(`ignore` = 0 AND (status_code >= 400 OR status_code < 200), 1, 0) AS broken,
                IF(`ignore` = 1, 1, 0) AS ignored
            ')
            ->when($type, function($query) use ($type) {
                switch ($type) {
                    case LinkStatusTypeEnum::OK:
                        $query
                            ->where('ignore', false)
                            ->where('status_code', '>=', 200)
                            ->where('status_code', '<', 300);
                        break;
                    case LinkStatusTypeEnum::REDIRECT:
                        $query
                            ->where('ignore', false)
                            ->where('status_code', '>=', 300)
                            ->where('status_code', '<', 400);
                        break;
                    case LinkStatusTypeEnum::BROKEN:
                        $query
                            ->where('ignore', false)
                            ->where(function ($q) {
                                $q->where('status_code', '<', 200)
                                    ->orWhere('status_code', '>=', 400);
                            });
                        break;
                    case LinkStatusTypeEnum::IGNORED:
                        $query->where('ignore', true);
                        break;
                }
            })
            ->orderBy('broken', 'desc')
            ->orderBy('redirect', 'desc')
            ->orderBy('last_checked_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

    }

}