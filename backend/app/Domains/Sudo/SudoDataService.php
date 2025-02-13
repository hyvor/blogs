<?php

namespace App\Domains\Sudo;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Collection;

class SudoDataService
{

    /**
     * @return Collection<int, Blog>
     */
    public static function Blogs(
        ?int $blogId,
        ?string $subdomain,
        string $sortBy,
        string $sort,
        int $limit,
        int $offset
    ): Collection {
        $sortBy = match ($sortBy) {
            default => 'id',
        };

        return Blog::with('variants')
            ->select('blogs.*')
            ->when($blogId, fn($query, $blogId) => $query->where('blogs.id', $blogId))
            ->when($subdomain, fn($query, $subdomain) => $query->where('blogs.subdomain', $subdomain))
            ->withCount('posts')
            ->orderBy($sortBy, $sort)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

}
