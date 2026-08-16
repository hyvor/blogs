<?php

namespace App\Api\Console\Input\Post\Suggestion;

use App\Entity\Enum\PostSuggestionType;
use Symfony\Component\Validator\Constraints as Assert;

class CreatePostSuggestionInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public string $id;

    #[Assert\NotNull]
    public PostSuggestionType $type;
}
