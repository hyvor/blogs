<?php

namespace App\Api\Console\Input\Blog\Redirect;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRedirectInput
{
    #[Assert\NotNull]
    public bool $dynamic;

    #[Assert\NotBlank]
    public string $path;

    #[Assert\NotBlank]
    #[Assert\Url]
    public string $to;

    #[Assert\NotBlank]
    #[Assert\Choice(['permanent', 'temporary'])]
    public string $type;
}
