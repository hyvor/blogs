<?php

namespace App\Domains\Sudo;

use App\Models\Blog;

class SudoDataService
{
    public static function Blogs(
        ?int $blogId,
        string $sortBy,
        string $sort,
        int $limit,
        int $offset
    ): mixed {
        $sortBy = match ($sortBy) {
            default => 'id',
        };

        return Blog::with('variants', 'subscriptions')
            ->select('blogs.*')
            ->when($blogId, fn($query, $blogId) => $query->where('blogs.id', $blogId))
            ->withCount('posts')
            ->orderBy($sortBy, $sort)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

}
