<?php

namespace App\Api\Console\Input\Post\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

class GetPostSuggestionsInput
{
    #[Assert\NotNull]
    public int $language_id;

    /** @var string[] */
    #[Assert\NotNull]
    #[Assert\Count(max: 200)]
    #[Assert\All([new Assert\Length(max: 64)])]
    public array $ids = [];
}
