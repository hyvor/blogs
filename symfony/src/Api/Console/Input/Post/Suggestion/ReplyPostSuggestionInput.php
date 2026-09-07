<?php

namespace App\Api\Console\Input\Post\Suggestion;

use App\Entity\Enum\PostSuggestionType;
use Symfony\Component\Validator\Constraints as Assert;

class ReplyPostSuggestionInput
{
    #[Assert\NotNull]
    public int $language_id;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public string $id;

    #[Assert\NotBlank]
    public string $content;

    // used only to auto-create the parent suggestion if this reply's create event
    // hasn't been persisted yet (see PostSuggestionService::reply())
    public ?PostSuggestionType $type = null;
}
