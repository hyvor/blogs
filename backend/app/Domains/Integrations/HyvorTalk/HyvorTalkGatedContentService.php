<?php

namespace App\Domains\Integrations\HyvorTalk;

use App\Domains\Integrations\HyvorTalk\Event\GatedContentChangedEvent;
use App\Models\Blog;
use App\Models\HyvorTalkGatedContentRule;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class HyvorTalkGatedContentService
{

    public const MAX_GATED_CONTENT_RULES = 5;

    public static function ruleByBlogAndTagId(Blog $blog, int $tagId) : ?HyvorTalkGatedContentRule
    {
        return HyvorTalkGatedContentRule::where('blog_id', $blog->id)
            ->where('tag_id', $tagId)
            ->first();
    }

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
        int $tagId,
        string $minimumPlan,
        ?string $gate,
    )
    {

        $rule = HyvorTalkGatedContentRule::create([
            'blog_id' => $blog->id,
            'tag_id' => $tagId,
            'minimum_plan' => $minimumPlan,
            'gate' => $gate
        ]);

        event(new GatedContentChangedEvent($blog));

        return $rule;

    }

    /**
     * @param array{ minimum_plan: string, gate: string|null } $updates
     */
    public static function updateGatedContentRule(Blog $blog, HyvorTalkGatedContentRule $rule, array $updates) : HyvorTalkGatedContentRule
    {
        $rule->update($updates);
        event(new GatedContentChangedEvent($blog));
        return $rule;
    }

    public static function deleteGatedContentRule(Blog $blog, HyvorTalkGatedContentRule $rule) : void
    {
        $rule->delete();
        event(new GatedContentChangedEvent($blog));
    }

    public static function gatePostIfNeeded(Blog $blog, Post $post) : bool
    {

        //

    }

}