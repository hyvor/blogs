<?php

namespace App\Api\Data\Input;

use Symfony\Component\Validator\Constraints as Assert;

class GetTagsInput
{
    public ?string $language = null;

    #[Assert\GreaterThanOrEqual(1)]
    #[Assert\LessThanOrEqual(250)]
    public ?int $limit = null;

    #[Assert\GreaterThanOrEqual(1)]
    public ?int $page = null;

    public ?string $filter = null;
    public ?string $sort = null;
    public ?string $keys = null;

    /** @var 'public'|'private'|'any' */
    #[Assert\Choice(choices: ['public', 'private', 'any'])]
    public string $visibility = 'public';
}
