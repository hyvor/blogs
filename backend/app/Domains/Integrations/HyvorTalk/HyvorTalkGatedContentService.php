<?php

namespace App\Domains\Integrations\HyvorTalk;

use App\Domains\Integrations\HyvorTalk\Event\GatedContentChangedEvent;
use App\Domains\Integrations\HyvorTalk\Exception\EncryptionKeyMissingException;
use App\Models\Blog;
use App\Models\HyvorTalkGatedContentRule;
use App\Models\HyvorTalkWebsite;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    /**
     * @return Collection<int, HyvorTalkGatedContentRule>
     */
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
    ) : HyvorTalkGatedContentRule
    {

        $rule = null;

        DB::transaction(function() use (&$rule, $blog, $tagId, $minimumPlan, $gate) {

            $rule = HyvorTalkGatedContentRule::create([
                'blog_id' => $blog->id,
                'tag_id' => $tagId,
                'minimum_plan' => $minimumPlan,
                'gate' => $gate
            ]);

            event(new GatedContentChangedEvent($blog));

        });

        /** @var HyvorTalkGatedContentRule $rule */
        return $rule;

    }

    /**
     * @param array{ minimum_plan?: string, gate?: string|null } $updates
     */
    public static function updateGatedContentRule(Blog $blog, HyvorTalkGatedContentRule $rule, array $updates) : HyvorTalkGatedContentRule
    {

        DB::transaction(function() use (&$rule, $updates, $blog) {

            $rule->update($updates);
            event(new GatedContentChangedEvent($blog));

        });

        return $rule;
    }

    public static function deleteGatedContentRule(Blog $blog, HyvorTalkGatedContentRule $rule) : void
    {
        DB::transaction(function() use ($rule, $blog) {
            $rule->delete();
            event(new GatedContentChangedEvent($blog));
        });
    }

    public static function getPostContentHtml(Blog $blog, Post $post, PostVariant $variant) : string
    {

        $hyvorTalkWebsite = $blog->hyvorTalkWebsite;

        if ($hyvorTalkWebsite) {
            $rules = $blog->hyvorTalkGatedContentRules;

            foreach ($rules as $rule) {
                // Check if the post has the tag that is gated
                if ($post->tags->contains($rule->tag_id)) {
                    try {
                        $secure = self::calculateGatedSecure($hyvorTalkWebsite, $rule, $variant->content_html);
                    } catch (EncryptionKeyMissingException) {
                        return 'Error: Hyvor Talk encryption key is missing.';
                    }
                    return '<hyvor-talk-gated-content secure="' . $secure . '"></hyvor-talk-gated-content>';
                }
            }
        }

        return $variant->content_html ?? '';

    }

    private static function calculateGatedSecure(HyvorTalkWebsite $hyvorTalkWebsite, HyvorTalkGatedContentRule $rule, ?string $content) : string
    {

        $key = $hyvorTalkWebsite->encryption_key;

        if (!$key) {
            throw new EncryptionKeyMissingException();
        }

        $data = [
            'timestamp' => time(),
            'content' => $content ?? '',
            'minimum-plan' => $rule->minimum_plan,
            'gate' => $rule->gate,
        ];

        $data = (string) json_encode($data);
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, $iv);

        return base64_encode((string) $encrypted) . ':' . base64_encode($iv);
    }

}