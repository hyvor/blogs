<?php

namespace App\Api\Console\Input\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class GetTagsInput
{
    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 50;

    #[Assert\PositiveOrZero]
    public int $offset = 0;

    public ?string $search = null;
}
