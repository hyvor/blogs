<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\LinkAnalyzerLink;
use Illuminate\Database\Eloquent\Collection;

class LinkAnalyzeService
{

    const IGNORE_CODE = -2;

    /**
     * Adds the ignore code to the results
     * This is the object shape that is used in the frontend
     *
     * @param Collection<int, LinkAnalyzerLink> $links
     * @return array<string, int>
     */
    public static function getIgnoreAwareStatusFromLinks(Collection $links): array
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
    public static function getCountsByStatus(Blog $blog): array
    {
        $counts = LinkAnalyzerLink::where('blog_id', $blog->id)
            ->selectRaw(
                '
                SUM(CASE WHEN ignore = false AND status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) AS ok,
                SUM(CASE WHEN ignore = false AND status_code >= 300 AND status_code < 400 THEN 1 ELSE 0 END) AS redirect,
                SUM(CASE WHEN ignore = false AND (status_code = 404 OR status_code = 0) THEN 1 ELSE 0 END) AS broken,
                SUM(CASE WHEN ignore = false AND (status_code != 404 AND status_code != 0 AND (status_code >= 400 OR status_code < 200)) THEN 1 ELSE 0 END) AS risky,
                SUM(CASE WHEN ignore = true THEN 1 ELSE 0 END) AS ignored
            '
            )
            ->first();

        if (!$counts) {
            throw new SafetyException();
        }

        /**
         * @var array{ok: integer, redirect: integer, broken: integer, risky: integer, ignored: integer} $counts
         */
        $counts = $counts->toArray();

        return [
            'ok' => (int)$counts['ok'],
            'redirect' => (int)$counts['redirect'],
            'broken' => (int)$counts['broken'],
            'risky' => (int)$counts['risky'],
            'ignored' => (int)$counts['ignored'],
        ];
    }

    /**
     * @return Collection<int, LinkAnalyzerLink>
     */
    public static function getLinksOfBlog(
        Blog $blog,
        ?LinkStatusTypeEnum $type,
        int $limit,
        int $offset
    ): Collection {
        return LinkAnalyzerLink::where('blog_id', $blog->id)
            ->with('postVariant', 'postVariant.language')
            ->selectRaw(
                '
                *,
                CASE WHEN ignore = false AND status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END AS ok,
                CASE WHEN ignore = false AND status_code >= 300 AND status_code < 400 THEN 1 ELSE 0 END AS redirect,
                CASE WHEN ignore = false AND (status_code = 404 OR status_code = 0) THEN 1 ELSE 0 END AS broken,
                CASE WHEN ignore = false AND (status_code != 404 AND status_code != 0 AND (status_code >= 400 OR status_code < 200)) THEN 1 ELSE 0 END AS risky,
                CASE WHEN ignore = true THEN 1 ELSE 0 END AS ignored
            '
            )
            ->when($type, function ($query) use ($type) {
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
                                $q->where('status_code', 404)
                                    ->orWhere('status_code', 0);
                            });
                        break;
                    case LinkStatusTypeEnum::RISKY:
                        $query
                            ->where('ignore', false)
                            ->where('status_code', '!=', 404)
                            ->where('status_code', '!=', 0)
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
            ->orderBy('risky', 'desc')
            ->orderBy('redirect', 'desc')
            ->orderBy('last_checked_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

}
