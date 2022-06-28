<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\ApiKeyObject;
use App\Domains\Api\ApiKeysRepository;
use App\Models\Blog;

class ConsoleApiKeysController
{

    public function getApiKeys(Blog $blog)
    {
        $keys = ApiKeysRepository::get($blog)->mapInto(ApiKeyObject::class);
        return response()->json($keys);
    }

    public function createApiKey()
    {

    }

    public function deleteApiKey()
    {

    }

}