<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use App\Domains\Post\PostRepository;
use App\Models\Blog;
use App\Models\LinkAnalyzerLink;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Collection;

class PostVariantLinkService
{

    public static function getLink(PostVariant $variant, string $url) : ?LinkAnalyzerLink
    {
        return LinkAnalyzerLink::where('post_variant_id', $variant->id)
            ->where('url', $url)
            ->first();
    }

    public static function ignoreLink(LinkAnalyzerLink $link, bool $status) : void
    {
        $link->ignore = $status;
        $link->save();
    }

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
     * @param AnalyzedLink[] $results
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

        foreach ($results as $result) {

            $link = LinkAnalyzerLink::updateOrCreate(
                [
                    'post_variant_id' => $variant->id,
                    'url' => $result->originalUrl,
                ],
                [
                    'full_url' => $result->url,
                    'blog_id' => $blog->id,
                    'last_checked_at' => $now,
                    'status_code' => $result->status,
                    'ignore' => in_array($result->originalUrl, $ignoreUrls),
                ]
            );

            $links[] = $link;
        }

        return Collection::make($links);
    }

    /**
     * @param PostVariant $variant
     * @param array<string, number> $results
     * @param bool $append
     * @return void
     */
    public static function updatePostVariantCache(
        PostVariant $variant,
        array $results,
        bool $append = false
    ) : void
    {
        $currentVariantResults = $variant->link_analysis ?? [];
        PostRepository::updatePostVariant($variant, [
            'link_analysis' => $append ? array_merge(
                $currentVariantResults,
                $results
            ) : $results
        ]);
    }

}