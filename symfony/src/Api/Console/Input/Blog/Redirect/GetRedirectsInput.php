<?php

namespace App\Api\Console\Input\Blog\Redirect;

use Symfony\Component\Validator\Constraints as Assert;

class GetRedirectsInput
{
    public ?string $search = null;

    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 25;

    #[Assert\PositiveOrZero]
    public int $offset = 0;
}
