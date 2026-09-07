<?php

namespace App\Api\Console\Input\Ai;

use Symfony\Component\Validator\Constraints as Assert;

class AgentPromptInput
{
    #[Assert\NotBlank]
    public string $prompt;

    public ?int $post_variant_id = null;

    public ?int $conversation_id = null;
}
