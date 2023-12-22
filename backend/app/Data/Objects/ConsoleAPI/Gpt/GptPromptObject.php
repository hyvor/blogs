<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\Gpt;

use App\Models\GptPrompt;

class GptPromptObject
{

    public int $id;
    public int $created_at;
    public ?int $post_id;

    public string $prompt;
    public string $gpt_response;

    public function __construct(GptPrompt $prompt)
    {
        $this->id = $prompt->id;
        $this->created_at = $prompt->created_at->getTimestamp();
        $this->post_id = $prompt->post_id;
        $this->prompt = $prompt->prompt;
        $this->gpt_response = strval($prompt->gpt_response);
    }

}