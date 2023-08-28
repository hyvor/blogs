<?php declare(strict_types=1);

namespace App\Domains\Integrations\OpenAi;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Domains\Subscription\SubscriptionService;
use App\Models\AutoTranslation;
use App\Models\Blog;
use App\Models\GptPrompt;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class GptPromptsService
{

    public static function createPrompt(
        Blog $blog,
        ?Post $post,
        string $prompt
    ) : GptPrompt
    {

        $promptCreator = new Prompt($post, $prompt);
        $response = $promptCreator->getResponse();

        $gptPromptModel = GptPrompt::create([
            'blog_id' => $blog->id,
            'post_id' => $post?->id,
            'prompt' => $prompt,
            'gpt_response' => $response->choices[0]->message->content,
            'model_name' => $response->model,
            'tokens_prompt' => $response->usage->promptTokens,
            'tokens_response' => $response->usage->completionTokens,
            'tokens_total' => $response->usage->totalTokens,
        ]);

        return $gptPromptModel->refresh();

    }


    /**
     * @return Collection<int, GptPrompt>
     */
    public static function getPromptsByPost(Post $post) : Collection
    {
        return GptPrompt::where('post_id', $post->id)
            ->limit(50)
            ->get();
    }

    public static function deletePromptsByPost(Post $post) : void
    {
        GptPrompt::where('post_id', $post->id)
            ->delete();
    }

    public static function getThisMonthUsage(Blog $blog) : int
    {
        return intval(GptPrompt::where('blog_id', $blog->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('tokens_total'));
    }

    public static function getMaxMonthlyGptTokens(Blog $blog, ?SubscriptionPlanEnum $plan) : int
    {
        if ($plan === null && $blog->trial_ends_at->isFuture())
            return 1000;

        return match ($plan) {
            SubscriptionPlanEnum::GROWTH => 100000,
            SubscriptionPlanEnum::PREMIUM => 1000000,
            SubscriptionPlanEnum::TEAM => 3000000,
            SubscriptionPlanEnum::BUSINESS => 15000000,
            SubscriptionPlanEnum::ENTERPRISE => 30000000,
            default => 0,
        };
    }

    public static function hasLimitsExceeded(Blog $blog) : bool
    {
        $subscription = SubscriptionService::getActiveBlogSubscription($blog);
        $plan = $subscription?->plan;

        $usage = self::getThisMonthUsage($blog);
        $maxUsage = self::getMaxMonthlyGptTokens($blog, $plan);

        return $usage >= $maxUsage;
    }

}