<?php

namespace App\Api\Console\Input\Blog\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class SearchTagsInput
{
    #[Assert\NotBlank]
    public string $search;
}
