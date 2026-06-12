<?php

namespace App\Api\Data\Input;

use Symfony\Component\Validator\Constraints as Assert;

class SearchPostsInput
{
    #[Assert\NotBlank]
    public string $search = '';

    public ?string $language = null;

    #[Assert\GreaterThanOrEqual(1)]
    public ?int $limit = null;

    #[Assert\GreaterThanOrEqual(1)]
    public ?int $page = null;

    public ?string $keys = null;
}
