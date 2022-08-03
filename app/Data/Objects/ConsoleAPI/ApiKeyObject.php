<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\ApiKeysTypeEnum;
use App\Models\ApiKey;

class ApiKeyObject
{
    public int $id;

    public string $name;

    public ApiKeysTypeEnum $type;

    public string $api_key; // len=32

    public function __construct(ApiKey $apiKey)
    {
        $this->id = $apiKey->id;
        $this->name = $apiKey->name;
        $this->type = $apiKey->type;
        $this->api_key = $apiKey->api_key;
    }
}
