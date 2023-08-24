<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use App\Models\Blog;
use App\Models\LinkAnalyzerLink;
use App\Models\PostVariant;
use Illuminate\Support\Collection;

class PostVariantLinkService
{

    /**
     * @return Collection<int, LinkAnalyzerLink>
     */
    public static function getIgnoredLinks(PostVariant $variant) : Collection
    {
        return LinkAnalyzerLink::where('post_variant_id', $variant->id)
            ->where('ignore', true)
            ->get();
    }

    /**
     * @param PostVariant $variant
     * @param array<string, integer> $results
     * @param string[] $ignoreUrls
     * @return Collection<int, LinkAnalyzerLink>
     */
    public static function updateLinksFromResults(
        Blog $blog,
        PostVariant $variant,
        array $results,
        bool $shouldClear = false,
        array $ignoreUrls = [],
    ) : Collection
    {

        $now = now();

        if ($shouldClear) {
            LinkAnalyzerLink::where('post_variant_id', $variant->id)
                ->delete();
        }

        $links = [];

        foreach ($results as $url => $statusCode) {

            $link = LinkAnalyzerLink::updateOrCreate(
                [
                    'post_variant_id' => $variant->id,
                    'url' => $url,
                ],
                [
                    'blog_id' => $blog->id,
                    'last_checked_at' => $now,
                    'status_code' => $statusCode,
                    'ignore' => in_array($url, $ignoreUrls),
                ]
            );

            $links[] = $link;
        }

        return collect($links);
    }

}