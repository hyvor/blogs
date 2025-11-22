<?php
declare(strict_types=1);

namespace App\Domains\Blog;

use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;
use Illuminate\Support\Str;

class TempBlogService
{

    public static function getTempBlog(?string $subdomain, ?string $ip): Blog
    {
        $blog = $subdomain ? BlogService::getBlogBySubdomain($subdomain) : null;

        if ($blog && $blog->type === BlogTypeEnum::TEMP) {
            return $blog;
        }

        $subdomain = 'temp-' . Str::random(24) . '-' . now()->getTimestamp();

        return app(BlogService::class)->createBlog(
            null,
            null,
            'Temporary Blog',
            $subdomain,
            BlogTypeEnum::TEMP,
            ip: $ip,
        );
    }

}