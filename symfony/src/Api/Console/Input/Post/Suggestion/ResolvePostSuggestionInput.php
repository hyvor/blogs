<?php

namespace App\Api\Console\Input\Post\Suggestion;

use App\Entity\Enum\PostSuggestionDecision;
use Symfony\Component\Validator\Constraints as Assert;

class ResolvePostSuggestionInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotNull]
    public PostSuggestionDecision $decision;
}
