<?php declare(strict_types=1);

namespace App\Domains\Integrations\OpenAi;

use App\Domains\Subscription\LicenseService;
use App\Models\Blog;
use App\Models\GptPrompt;
use App\Models\Post;
use Hyvor\Internal\Billing\License\BlogsLicense;
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
        return intval(GptPrompt::withTrashed()
            ->where('blog_id', $blog->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('tokens_total'));
    }

    public static function getMaxMonthlyGptTokens(BlogsLicense $blogsLicense) : int
    {
        return $blogsLicense->aiTokensK * 1000;
    }

    public static function hasLimitsExceeded(Blog $blog) : bool
    {

        $license = LicenseService::getLicense($blog);
        if (!$license) {
            return true;
        }

        $maxUsage = self::getMaxMonthlyGptTokens($license);
        $usage = self::getThisMonthUsage($blog);

        return $usage >= $maxUsage;
    }

}
