<?php declare(strict_types=1);

namespace App\Domains\Integrations\OpenAi;

use App\Models\GptPrompt;
use App\Models\Post;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Yethee\Tiktoken\EncoderProvider;

class Prompt
{

    const INPUT_MAX_TOKENS = 1000;
    const OUTPUT_MAX_TOKENS = 3000;

    const SYSTEM_PROMPT =
        'You are a helpful AI assistant. ' .
        'You help users of Hyvor Blogs blogging platform (blogs.hyvor.com) create content.' .
        'Reply in markdown format.' .
        'First level of headings is h2.';

    const GPT_MODEL = 'gpt-4o-mini';

    public function __construct(
        private ?Post $post,
        private string $prompt,
    ) {}

    public function getResponse() : CreateResponse
    {

        $userOldPrompts = array_map(fn (string $prompt) => [
            'role' => 'user',
            'content' => $prompt
        ], $this->getUserOldPrompts());

        return OpenAI::chat()->create([
            'model' => self::GPT_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => self::SYSTEM_PROMPT
                ],
                ...$userOldPrompts,
                [
                    'role' => 'user',
                    'content' => $this->prompt
                ]
            ],
            'max_tokens' => self::OUTPUT_MAX_TOKENS,
        ]);

    }

    /**
     * @return string[]
     */
    private function getUserOldPrompts() : array
    {

        $result = [];

        $currentPromptTokens = $this->getTokenLength($this->prompt);
        $systemPromptTokens = $this->getTokenLength(self::SYSTEM_PROMPT);

        $remainingTokens = self::INPUT_MAX_TOKENS - $currentPromptTokens - $systemPromptTokens;

        $promptsOnPost = $this->post ?
            GptPrompt::where('post_id', $this->post->id)
                ->limit(50)
                ->orderBy('id', 'DESC')
                ->get() :
            collect();

        foreach ($promptsOnPost as $prompt) {
            $tokens = $this->getTokenLength($prompt->prompt);
            if ($tokens > $remainingTokens) {
                break;
            }
            $result[] = $prompt->prompt;
            $remainingTokens -= $tokens;
        }

        return $result;
    }

    private function getTokenLength(string $prompt) : int
    {
        // tiktoken-php was too slow
        $words = count(explode(' ', $prompt));
        return (int) ceil($words * 0.7);
    }

}