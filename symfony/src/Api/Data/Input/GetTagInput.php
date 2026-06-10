<?php

namespace App\Api\Data\Input;

use Symfony\Component\Validator\Constraints as Assert;

class GetTagInput
{
    #[Assert\Positive]
    public ?int $id = null;

    public ?string $slug = null;
    public ?string $language = null;
    public ?string $keys = null;
}
