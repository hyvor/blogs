<?php

namespace App\Api\Console\Input\Redirect;

use App\Entity\Enum\RedirectType;
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

    #[Assert\NotNull]
    public RedirectType $type;
}
