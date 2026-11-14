<?php

namespace App\Api\Console\Object;

use App\Api\Console\Authorization\Scope;
use App\Entity\ApiKey;

class ApiKeyObject
{
    public int $id;
    public string $name;
    public string $type;
    public string $api_key;

    /** @var string[] */
    public array $scopes;

    public function __construct(ApiKey $apiKey)
    {
        $this->id = $apiKey->getId();
        $this->name = $apiKey->getName();
        $this->type = $apiKey->getType()->value;
        $this->api_key = $apiKey->getApiKey();
        $this->scopes = array_map(fn(Scope $scope) => $scope->value, $apiKey->getScopes());
    }
}
