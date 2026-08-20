<?php

namespace App\Api\Console\Input\Post\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

class ResolveAuthorInput
{
    #[Assert\NotNull]
    public int $hyvor_user_id;
}
