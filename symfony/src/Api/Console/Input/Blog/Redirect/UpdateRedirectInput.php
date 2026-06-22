<?php

namespace App\Api\Console\Input\Blog\Redirect;

use App\Entity\Enum\RedirectType;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateRedirectInput
{
    public ?string $path = null;

    #[Assert\Url]
    public ?string $to = null;

    public ?RedirectType $type = null;
}
