<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Gpt\GptPromptObject;
use App\Domains\Integrations\OpenAi\GptPromptsService;
use App\Domains\Post\PostRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleGptController
{

    private function getPost(Request $request) : Post
    {
        $postId = $request->integer('post_id');
        $post = PostRepository::getPostById($postId);
        if (!$post) {
            throw new TrustedException('Post not found');
        }
        if ($post->blog_id !== app(Blog::class)->id) {
            throw new TrustedException('Post does not belong to blog');
        }
        return $post;
    }

    public function newPrompt(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'post_id' => 'required|integer',
            'prompt' => 'required|string|max:1000'
        ]);

        $post = $this->getPost($request);

        $prompt = GptPromptsService::createPrompt(
            $blog,
            $post,
            (string) $request->string('prompt')
        );

        return response()->json(new GptPromptObject($prompt));

    }

    public function getPostChatHistory(Request $request) : JsonResponse
    {
        $request->validate([
            'post_id' => 'required|integer'
        ]);

        $post = $this->getPost($request);

        $prompts = GptPromptsService::getPromptsByPost($post)
            ->mapInto(GptPromptObject::class);

        return response()->json($prompts);
    }

    public function deletePostChatHistory(Request $request) : JsonResponse
    {
        $request->validate([
            'post_id' => 'required|integer'
        ]);

        $post = $this->getPost($request);

        GptPromptsService::deletePromptsByPost($post);

        return response()->json();
    }

}