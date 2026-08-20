<?php

namespace App\Api\Console\Input\Post\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

class EditPostSuggestionReplyInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotBlank]
    public string $content;
}
