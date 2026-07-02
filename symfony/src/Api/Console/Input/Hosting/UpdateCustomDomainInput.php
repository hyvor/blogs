<?php

namespace App\Api\Console\Input\Hosting;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCustomDomainInput
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $domain;
}
