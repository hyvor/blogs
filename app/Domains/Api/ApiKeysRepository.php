<?php

namespace App\Domains\Api;

use App\Data\Enums\ApiKeysTypeEnum;
use App\Models\ApiKey;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Collection;

class ApiKeysRepository
{
    public static function get(Blog $blog): Collection
    {
        return ApiKey::where('blog_id', $blog->id)->get();
    }

    public static function create(Blog $blog, string $name, ApiKeysTypeEnum $type): ApiKey
    {
        $key = bin2hex(random_bytes(16));

        return ApiKey::create([
            'blog_id' => $blog->id,
            'api_key' => $key,
            'name' => $name,
            'type' => $type,
        ]);
    }

    public static function delete(ApiKey $apiKey)
    {
        $apiKey->delete();
    }

    public static function hasKey(Blog $blog, ApiKeysTypeEnum $type, string $key): bool
    {
        return ApiKey::where('blog_id', $blog->id)
            ->where('type', $type)
            ->where('api_key', $key)
            ->exists();
    }
}
