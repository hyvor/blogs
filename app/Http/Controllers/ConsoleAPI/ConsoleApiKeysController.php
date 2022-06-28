<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\ApiKeysTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Data\Objects\ConsoleAPI\ApiKeyObject;
use App\Domains\Api\ApiKeysRepository;
use App\Models\ApiKey;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleApiKeysController
{

    public function getApiKeys(Blog $blog)
    {
        $keys = ApiKeysRepository::get($blog)->mapInto(ApiKeyObject::class);
        return response()->json($keys);
    }

    public function createApiKey(Blog $blog, Request $request)
    {
        $request->validate([
            'type' => ['required', new Enum(ApiKeysTypeEnum::class)]
        ]);

        $type = ApiKeysTypeEnum::from($request->input('type'));
        $apiKey = ApiKeysRepository::create($blog, $type);

        return response()->json(new ApiKeyObject($apiKey));
    }

    public function deleteApiKey(ApiKey $apiKey)
    {
        ApiKeysRepository::delete($apiKey);

        return response()->json();
    }

}