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
            'credits_this_month' => 'this_month_pageviews_count',
            'credits_last_month' => 'month_1_ago_pageviews_count',
            default => 'id',
        };

        return Blog::with('variants', 'subscriptions')
            ->orderBy($sortBy, $sort)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

}
