<?php

namespace App\Domains\Integrations\HyvorTalk;

use App\Models\Blog;
use App\Models\HyvorTalkGatedContentRule;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class HyvorTalkGatedContentService
{

    public const MAX_GATED_CONTENT_RULES = 5;

    public static function getGatedContentRulesCount(Blog $blog) : int
    {
        return HyvorTalkGatedContentRule::where('blog_id', $blog->id)->count();
    }

    public static function getGatedContentRules(
        Blog $blog,
        bool $withTag = false
    ) : Collection
    {
        return HyvorTalkGatedContentRule::where('blog_id', $blog->id)
            ->when($withTag, function($query) {
                return $query->with('tag');
            })
            ->orderBy('id')
            ->limit(self::MAX_GATED_CONTENT_RULES)
            ->get();
    }

    public static function createGatedContentRule(
        Blog $blog,
        ?int $tagId,
        string $minimumPlan,
        ?string $gate,
    )
    {

        return HyvorTalkGatedContentRule::create([
            'blog_id' => $blog->id,
            'tag_id' => $tagId,
            'minimum_plan' => $minimumPlan,
            'gate' => $gate
        ]);

    }

    public static function gatePostIfNeeded(Blog $blog, Post $post) : bool
    {

        //

    }

}