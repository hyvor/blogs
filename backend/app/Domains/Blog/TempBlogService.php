<?php declare(strict_types=1);

namespace App\Domains\Blog;

use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;
use Illuminate\Support\Str;

class TempBlogService
{

    public static function getTempBlog(string $uniqueId) : Blog
    {

        $blog = Blog::where('temp_unique_id', $uniqueId)->first();

        if ($blog)
            return $blog;

        $subdomain = 'temp-' . Str::random(24) . '-' . now()->getTimestamp();

        return app(BlogService::class)->createBlog(
            null,
            'Temporary Blog',
            $subdomain,
            BlogTypeEnum::TEMP,
            tempUniqueId: $uniqueId
        );

    }

}