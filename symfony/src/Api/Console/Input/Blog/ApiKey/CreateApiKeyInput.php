<?php

namespace App\Api\Console\Input\Blog\ApiKey;

use App\Entity\Enum\ApiKeyType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateApiKeyInput
{
    #[Assert\NotBlank]
    public string $name;

    public ApiKeyType $type;
}
