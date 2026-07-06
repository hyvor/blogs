<?php

namespace App\Api\Console\Input\ApiKey;

use App\Entity\Enum\ApiKeyType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateApiKeyInput
{
    #[Assert\NotBlank]
    public string $name;

    public ApiKeyType $type;
}
