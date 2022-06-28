<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\ApiTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Models\ApiKey;

class ApiKeyObject
{

    public int $id;
    public ApiTypeEnum $type;
    public string $api_key; // len=32
    public ?UserRoleEnum $role;

    public function __construct(ApiKey $apiKey)
    {
        $this->id = $apiKey->id;
        $this->type = $apiKey->type;
        $this->api_key = $apiKey->api_key;
        $this->role = $apiKey->role;
    }

}