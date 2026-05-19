<?php

namespace App\Api\Console\Input\Blog\ApiKey;

use Symfony\Component\Validator\Constraints as Assert;

class CreateApiKeyInput
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Choice(['console', 'delivery'])]
    public string $type;
}
