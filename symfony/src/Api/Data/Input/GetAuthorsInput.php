<?php

namespace App\Api\Data\Input;

use Symfony\Component\Validator\Constraints as Assert;

class GetAuthorsInput
{
    public ?string $language = null;

    #[Assert\GreaterThanOrEqual(1)]
    public ?int $limit = null;

    #[Assert\GreaterThanOrEqual(1)]
    public ?int $page = null;

    public ?string $filter = null;
    public ?string $sort = null;
    public ?string $keys = null;
}
