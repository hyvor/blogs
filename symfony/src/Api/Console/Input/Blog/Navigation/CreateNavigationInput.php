<?php

namespace App\Api\Console\Input\Blog\Navigation;

use Symfony\Component\Validator\Constraints as Assert;

class CreateNavigationInput
{
    #[Assert\NotBlank]
    public string $url;

    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['header', 'footer'])]
    public string $type;
}
