<?php

namespace App\Domains\Sudo;

use App\Models\Blog;

class SudoDataService
{
    public static function Blogs(
        ?int $blogId,
        ?string $subdomain,
        string $sortBy,
        string $sort,
        ?string $filter,
        int $limit,
        int $offset
    ): mixed {
        $sortBy = match ($sortBy) {
            default => 'id',
        };
        
        return Blog::with('variants', 'subscriptions')
            ->select('blogs.*')
            ->when($blogId, fn($query, $blogId) => $query->where('blogs.id', $blogId))
            ->when($subdomain, fn($query, $subdomain) => $query->where('blogs.subdomain', $subdomain))
            ->when($filter === 'in_trial', function ($query) {
                $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now());
            })
            ->when($filter === 'starter' || $filter === 'growth', function ($query) use ($filter) {
                $query->join('subscriptions', 'subscriptions.blog_id', '=', 'blogs.id')
                    ->where('subscriptions.plan', 'LIKE', "$filter%");
            })
            ->withCount('posts')
            ->orderBy($sortBy, $sort)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

}
