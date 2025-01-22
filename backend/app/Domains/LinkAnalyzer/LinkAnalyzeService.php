<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

// checks if the given links are broken or not
use App\Domains\Route\PermalinkRepository;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\LinkAnalyzerLink;
use App\Models\PostVariant;
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
     * @return AnalyzedLink[]
     */
    public static function analyzePostVariantLinks(
        Blog $blog,
        PostVariant $variant,
        array $urls
    ) : array
    {

        $post = $variant->post;
        $language = $variant->language;

        if (!$post || !$language) {
            throw new SafetyException('Post or language not found');
        }

        $variantUrl = PermalinkRepository::getPostPermalink($post, $blog, $language);

        $urlMap = [];
        $fullUrls = [];

        foreach ($urls as $url) {
            $fullUrl = FullUrl::getFullUrl($url, $variantUrl);
            if (!$fullUrl)
                continue;
            $urlMap[$fullUrl] = $url;
            $fullUrls[] = $fullUrl;
        }

        $results = self::analyze($fullUrls);

        return array_map(fn (string $key, int $status) => new AnalyzedLink(
            $urlMap[$key],
            $key,
            $status,
        ), array_keys($results), array_values($results));

    }


    /**
     * When given a list of complete URLs, returns an array with the status code of each URL
     *
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
                SUM(CASE WHEN ignore = false AND status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) AS ok,
                SUM(CASE WHEN ignore = false AND status_code >= 300 AND status_code < 400 THEN 1 ELSE 0 END) AS redirect,
                SUM(CASE WHEN ignore = false AND (status_code >= 400 OR status_code < 200) THEN 1 ELSE 0 END) AS broken,
                SUM(CASE WHEN ignore = true THEN 1 ELSE 0 END) AS ignored
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
                CASE WHEN ignore = false AND status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END AS ok,
                CASE WHEN ignore = false AND status_code >= 300 AND status_code < 400 THEN 1 ELSE 0 END AS redirect,
                CASE WHEN ignore = false AND (status_code >= 400 OR status_code < 200) THEN 1 ELSE 0 END AS broken,
                CASE WHEN ignore = true THEN 1 ELSE 0 END AS ignored
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
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

    }

}
