<?php

namespace App\Domains\Api;

use App\Data\Enums\ApiKeysTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Models\ApiKey;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Collection;

class ApiKeysRepository
{

    public static function get(Blog $blog) : Collection
    {
        return ApiKey::where('blog_id', $blog->id)->get();
    }

    public static function create(Blog $blog, ApiKeysTypeEnum $type) : ApiKey
    {

        $key = bin2hex(random_bytes(16));

        return ApiKey::create([
            'blog_id' => $blog->id,
            'api_key' => $key,
            'type' => $type
        ]);
    }

    public static function delete(ApiKey $apiKey)
    {
        $apiKey->delete();
    }

}