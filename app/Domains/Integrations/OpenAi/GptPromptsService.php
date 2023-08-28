<?php declare(strict_types=1);

namespace App\Domains\Gpt;

use App\Models\GptPrompt;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class GptPromptsService
{

    public static function 


    /**
     * @return Collection<int, GptPrompt>
     */
    public static function getPromptsByPost(Post $post) : Collection
    {
        return GptPrompt::where('post_id', $post->id)
            ->limit(50)
            ->get();
    }

}