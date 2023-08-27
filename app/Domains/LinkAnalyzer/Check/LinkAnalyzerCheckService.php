<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Data\Enums\JobStatusEnum;
use App\Models\Blog;
use App\Models\LinkAnalyzerCheck;

class LinkAnalyzerCheckService
{

    public static function getLastCheck(Blog $blog) : ?LinkAnalyzerCheck
    {
        return LinkAnalyzerCheck::where('blog_id', $blog->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public static function createCheck(Blog $blog) : LinkAnalyzerCheck
    {
        $pending = LinkAnalyzerCheck::where('blog_id', $blog->id)
            ->where('status', JobStatusEnum::PENDING)
            ->first();

        if ($pending) {
            throw new LinkAnalyzerCheckException('Link analyzer check is already pending for this blog');
        }

        return LinkAnalyzerCheck::create([
            'blog_id' => $blog->id,
        ]);
    }

    public static function completeCheck(LinkAnalyzerCheck $check, FullBlogAnalyzer $analyze) : void
    {
        $check->update([
            'status' => JobStatusEnum::COMPLETED,
            'posts_count' => $analyze->postsCount,
            'post_variants_count' => $analyze->postVariantsCount,
            'links_total_count' => $analyze->linksCount,
            'links_ok_count' => $analyze->linksOkCount,
            'links_broken_count' => $analyze->linksBrokenCount,
            'links_redirect_count' => $analyze->linksRedirectCount,
            'links_ignored_count' => $analyze->linksIgnoredCount,
        ]);
    }
    public static function failCheck(LinkAnalyzerCheck $check, string $error) : void
    {
        $check->update([
            'status' => JobStatusEnum::FAILED,
            'error' => $error,
        ]);
    }

}