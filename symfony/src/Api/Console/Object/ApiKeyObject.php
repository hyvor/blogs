<?php

namespace App\Api\Console\Object;

use App\Entity\ApiKey;

class ApiKeyObject
{
    public int $id;
    public string $name;
    public string $type;
    public string $api_key;

    public function __construct(ApiKey $apiKey)
    {
        $this->id = $apiKey->getId();
        $this->name = $apiKey->getName();
        $this->type = $apiKey->getType()->value;
        $this->api_key = $apiKey->getApiKey();
    }
}
