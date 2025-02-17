<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Data\Enums\JobStatusEnum;
use App\Models\Blog;
use App\Models\LinkAnalyzerCheck;
use Illuminate\Database\Eloquent\Collection;

class LinkAnalyzerCheckService
{

    /**
     * @return Collection<int, LinkAnalyzerCheck>
     */
    public static function getChecks(Blog $blog, int $limit, int $offfset): Collection
    {
        return LinkAnalyzerCheck::where('blog_id', $blog->id)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->offset($offfset)
            ->get();
    }

    public static function getLastCheck(Blog $blog): ?LinkAnalyzerCheck
    {
        return LinkAnalyzerCheck::where('blog_id', $blog->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public static function createCheck(Blog $blog): LinkAnalyzerCheck
    {
        $pending = LinkAnalyzerCheck::where('blog_id', $blog->id)
            ->where('status', JobStatusEnum::PENDING)
            ->first();

        if ($pending) {
            throw new LinkAnalyzerCheckException('Link analyzer check is already pending for this blog');
        }

        $check = LinkAnalyzerCheck::create([
            'blog_id' => $blog->id,
        ]);

        return $check->refresh();
    }

    public static function completeCheck(LinkAnalyzerCheck $check, FullBlogAnalyzer $analyze): void
    {
        $check->update([
            'status' => JobStatusEnum::COMPLETED,
            'posts_count' => $analyze->postsCount,
            'links_total_count' => $analyze->linksCount,
            'links_ok_count' => $analyze->linksOkCount,
            'links_broken_count' => $analyze->linksBrokenCount,
            'links_risky_count' => $analyze->linksRiskyCount,
            'links_redirect_count' => $analyze->linksRedirectCount,
            'links_ignored_count' => $analyze->linksIgnoredCount,
        ]);
    }

    public static function failCheck(LinkAnalyzerCheck $check, string $error): void
    {
        $check->update([
            'status' => JobStatusEnum::FAILED,
            'error' => $error,
        ]);
    }

}