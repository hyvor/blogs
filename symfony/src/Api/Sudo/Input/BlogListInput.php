<?php

namespace App\Api\Sudo\Input;

use Symfony\Component\Validator\Constraints as Assert;

class BlogListInput
{
    public ?int $blog_id = null;

    public ?string $subdomain = null;

    public ?int $user_id = null;

    #[Assert\Choice(choices: ['asc', 'desc'])]
    public string $sort = 'desc';

    #[Assert\Positive]
    #[Assert\LessThan(100)]
    public int $limit = 30;

    #[Assert\PositiveOrZero]
    public int $offset = 0;
}
