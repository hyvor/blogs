<?php

namespace App\Api\Console\Input\Ai;

use Symfony\Component\Validator\Constraints as Assert;

class AgentPromptInput
{
    #[Assert\NotBlank]
    public string $prompt;
}
