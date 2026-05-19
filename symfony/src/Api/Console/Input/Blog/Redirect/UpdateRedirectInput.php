<?php

namespace App\Api\Console\Input\Blog\Redirect;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateRedirectInput
{
    public ?string $path = null;

    #[Assert\Url]
    public ?string $to = null;

    #[Assert\Choice(['permanent', 'temporary'])]
    public ?string $type = null;
}
