<?php

namespace App\Api\Console\Input\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class SearchTagsInput
{
    #[Assert\NotBlank]
    public string $search;
}
