<?php

namespace App\Domains\Sudo;

use App\Models\Blog;

class SudoDataService
{
    public static function Blogs(
        string $sortBy,
        string $sort,
        int $limit,
        int $offset
    ): mixed {
        $sortBy = match ($sortBy) {
            default => 'id',
        };

        return Blog::with('variants', 'subscriptions')
            ->withCount('posts')
            ->orderBy($sortBy, $sort)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

}
