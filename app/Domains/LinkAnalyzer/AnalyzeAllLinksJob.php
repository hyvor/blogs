<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use App\Models\Blog;
use App\Models\LinkAnalyzerCheck;

class AnalyzeAllLinksJob
{

    public function __construct(
        private Blog $blog
    ) {}

    public function handle() : void
    {

        $check = LinkAnalyzerCheck::create([
            'blog_id' => $this->blog->id,
        ]);

        $analyze = new AnalyzeAllLinks($this->blog);
        $analyze->analyze();

        $check->update([
            'posts_count' => $analyze->postsCount,
            'post_variants_count' => $analyze->postVariantsCount,
            'links_total_count' => $analyze->linksCount,
            'links_ok_count' => $analyze->linksOkCount,
            'links_broken_count' => $analyze->linksBrokenCount,
            'links_redirect_count' => $analyze->linksRedirectCount,
            'links_ignored_count' => $analyze->linksIgnoredCount,
        ]);

    }

}