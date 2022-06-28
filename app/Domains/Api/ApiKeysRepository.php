<?php

namespace App\Domains\Api;

use App\Data\Enums\ApiTypeEnum;
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

    public static function create(Blog $blog, ApiTypeEnum $type, ?UserRoleEnum $role) : ApiKey
    {
        return ApiKey::create([
            'blog_id' => $blog->id,
            'type' => $type,
            'role' => $role
        ]);
    }

    public static function delete(ApiKey $apiKey)
    {
        $apiKey->delete();
    }

}