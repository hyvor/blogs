<?php

namespace App\Api\Console\Input\ApiKey;

use App\Api\Console\Authorization\Scope;
use App\Entity\Enum\ApiKeyType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateApiKeyInput
{
    #[Assert\NotBlank]
    public string $name;

    public ApiKeyType $type;

    /**
     * Only used for console API keys
     * @var Scope[] $scopes
     */
    #[Assert\All(new Assert\Type(Scope::class))]
    public array $scopes = [];
}
